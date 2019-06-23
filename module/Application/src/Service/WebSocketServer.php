<?php

namespace Application\Service;


use Application\Exception\ExceptionWebSocketServer;
use Application\FormFilter\Message as FilterMessage;
use Application\Repository\Message;
use Exception;
use RuntimeException;

/**
 * Class WebSocketServer
 *
 * @package Application\Service
 */
class WebSocketServer
{
    /**
     * @var resource
     */
    private $socket;

    /**
     * @var array
     */
    private $config;

    /**
     * @var int
     */
    const COUNT_USERS = 20;

    /**
     * @var FilterMessage
     */
    private $form;

    /**
     * WebSocketServer constructor.
     *
     * @param  array  $config
     *
     * @throws ExceptionWebSocketServer
     */
    public function __construct(array $config)
    {
        if (!$config) {
            throw new ExceptionWebSocketServer('Required parameters are in corrupted.');
        }
        $this->config = $config;
        $this->runServer();
        $this->form = new FilterMessage();
    }

    /**
     * Run Socket Server
     */
    public function runServer()
    {
        ob_implicit_flush();
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_bind($this->socket, $this->config['web_socket']['host'], $this->config['web_socket']['port']);
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_listen($this->socket, self::COUNT_USERS);
    }

    /**
     * @param  Message  $message
     *
     * @throws Exception
     */
    public function acceptMessages(Message $message)
    {
        $connects = [];
        while (true) {
            $read = $connects;
            $read[] = $this->socket;
            $write = null;
            $except = null;
            if (socket_select($read, $write, $except, null) === false) {
                break;
            }
            if (in_array($this->socket, $read)) {
                $connect = socket_accept($this->socket);
                $header = socket_read($connect, 2048);
                $upgrade = $this->sendHandshake($header);
                socket_write($connect, $upgrade, strlen($upgrade));
                $msg = json_encode(['row' => '']);
                $new_msg = $this->encodeMessage($msg);
                socket_write($connect, $new_msg, strlen($new_msg));
                $connects[] = $connect;
                unset($read[array_search($this->socket, $read)]);
            }
            foreach ($read as $connect) {
                $data = socket_read($connect, 2048);
                if (! $data) {
                    socket_close($connect);
                    unset($connects[array_search($connect, $connects)]);
                    echo "Server Close Connect\n";
                    continue;
                }
                $decode_data = $this->decodeMessage($data);
                $form_data = ['message' => (string)$decode_data['payload'] ?? ''];
                $filter = $this->form->getFilter();
                $filter->setData($form_data);
                $row = '';
                if ($filter->isValid()) {
                    try {
                        $entity = $message->getEntity();
                        $entity->setText($filter->getValues()['message'] ?? '');
                        $entity->setCreated((new \DateTime())->format('Y-m-d H:i:s'));
                        $entity->setUserIp('127.0.0.1');
                        $row_id = $message->save();
                        $row = $message->getById($row_id);
                    } catch (RuntimeException $runtimeException) {
                        $row = '';
                    }
                    $msg = json_encode(compact('row'));
                } else {
                    $msg = json_encode(compact('row'));
                }
                $msg = $this->encodeMessage($msg);
                $filterConnections = array_filter($connects, function ($item) use ($connect) {
                    return ($item != $connect);
                });
                foreach ($filterConnections as $filterConnection) {
                    socket_write($filterConnection, $msg, strlen($msg));
                }
                socket_write($connect, $msg, strlen($msg));
            }
        }
        socket_close($this->socket);
    }

    /**
     * @return void
     */
    public function closeServer()
    {
        socket_close($this->socket);
    }

    /**
     * Return headers for clients
     *
     * @param $header
     *
     * @return string
     */
    private function sendHandshake($header)
    {
        $key = null;
        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $header, $match)) {
            $key = $match[1];
        };
        $SecWebSocketAccept = base64_encode(pack('H*',
            sha1($key.'258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        $upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n".
            "Upgrade: websocket\r\n".
            "Connection: Upgrade\r\n".
            "Sec-WebSocket-Accept:$SecWebSocketAccept\r\n\r\n";
        return $upgrade;
    }

    /**
     * @param $data
     *
     * @return array|bool
     */
    public function decodeMessage($data)
    {
        $unmaskedPayload = '';
        $decodedData = [];
        $firstByteBinary = sprintf('%08b', ord($data[0]));
        $secondByteBinary = sprintf('%08b', ord($data[1]));
        $opcode = bindec(substr($firstByteBinary, 4, 4));
        $isMasked = ($secondByteBinary[0] == '1') ? true : false;
        $payloadLength = ord($data[1]) & 127;
        if (! $isMasked) {
            return ['type' => '', 'payload' => '', 'error' => 'protocol error (1002)'];
        }

        switch ($opcode) {
            case 1:
                $decodedData['type'] = 'text';
                break;

            case 2:
                $decodedData['type'] = 'binary';
                break;

            case 8:
                $decodedData['type'] = 'close';
                break;

            case 9:
                $decodedData['type'] = 'ping';
                break;

            case 10:
                $decodedData['type'] = 'pong';
                break;
            default:
                return ['type' => '', 'payload' => '', 'error' => 'unknown opcode (1003)'];
        }

        if ($payloadLength === 126) {
            $mask = substr($data, 4, 4);
            $payloadOffset = 8;
            $dataLength = bindec(sprintf('%08b', ord($data[2])).sprintf('%08b', ord($data[3]))) + $payloadOffset;
        } elseif ($payloadLength === 127) {
            $mask = substr($data, 10, 4);
            $payloadOffset = 14;
            $tmp = '';
            for ($i = 0; $i < 8; $i++) {
                $tmp .= sprintf('%08b', ord($data[$i + 2]));
            }
            $dataLength = bindec($tmp) + $payloadOffset;
            unset($tmp);
        } else {
            $mask = substr($data, 2, 4);
            $payloadOffset = 6;
            $dataLength = $payloadLength + $payloadOffset;
        }
        if (strlen($data) < $dataLength) {
            return false;
        }
        if ($isMasked) {
            for ($i = $payloadOffset; $i < $dataLength; $i++) {
                $j = $i - $payloadOffset;
                if (isset($data[$i])) {
                    $unmaskedPayload .= $data[$i] ^ $mask[$j % 4];
                }
            }
            $decodedData['payload'] = $unmaskedPayload;
        } else {
            $payloadOffset = $payloadOffset - 4;
            $decodedData['payload'] = substr($data, $payloadOffset);
        }
        return $decodedData;
    }

    /**
     * @param $payload
     * @param  string  $type
     * @param  bool  $masked
     *
     * @return array|string
     */
    public function encodeMessage($payload, $type = 'text', $masked = false)
    {
        $frameHead = array();
        $payloadLength = strlen($payload);

        switch ($type) {
            case 'text':
                $frameHead[0] = 129;
                break;

            case 'close':
                $frameHead[0] = 136;
                break;

            case 'ping':
                $frameHead[0] = 137;
                break;

            case 'pong':
                $frameHead[0] = 138;
                break;
        }

        if ($payloadLength > 65535) {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for ($i = 0; $i < 8; $i++) {
                $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
            }
            if ($frameHead[2] > 127) {
                return ['type'    => '',
                        'payload' => '',
                        'error'   => 'frame too large (1004)'
                ];
            }
        } elseif ($payloadLength > 125) {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        } else {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }

        foreach (array_keys($frameHead) as $i) {
            $frameHead[$i] = chr($frameHead[$i]);
        }
        if ($masked === true) {
            $mask = array();
            for ($i = 0; $i < 4; $i++) {
                $mask[$i] = chr(rand(0, 255));
            }
            $frameHead = array_merge($frameHead, $mask);
        }
        $frame = implode('', $frameHead);
        for ($i = 0; $i < $payloadLength; $i++) {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }
        return $frame;
    }
}