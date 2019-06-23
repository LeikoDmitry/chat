(function ($) {
    $.fn.chat = function() {
       this.socket = init();
       this.on('submit', () => {
           let value = this.find('input[type=text]').val();
           if (value !== '') {
               this.find('input[type=text]').val('');
               return submitForm(this.socket, value);
           }
           return false;
       });
    };
    function init() {
        let socket = new WebSocket('ws://' + window.location.hostname + ':8000');
        socket.addEventListener('open', (e) => {
            $('.connect').show();
        });
        socket.addEventListener('error', (e) => {
            $('.disconnect').show();
        });
        socket.addEventListener('close', (e) => {
            console.log(e);
        });
        socket.addEventListener('message', (e) => {
            let socketMessage = JSON.parse(e.data);
            if (typeof socketMessage !== 'undefined') {
                let table = $('.table');
                let tbody = '';
                if (typeof socketMessage.row.created !== 'undefined' && typeof socketMessage.row.text !== 'undefined') {
                    tbody +=  "<tr>" +
                        "<td>" + socketMessage.row.created +" </td> \
                                <td>" + socketMessage.row.text + "</td> \
                           </tr>";
                    table.find('tbody').prepend(tbody);
                }
            }
        });
        return socket;
    }

    function submitForm(socket, data) {
        socket.send(data);
        return false;
    }
}(jQuery));

$(document).ready(() => {
    $('#chat-form').chat();
    function checkMessage() {
        let request = $.ajax({
            url: '/check',
            type: 'post',
            data: {code: "check"}
        });
        request.done((e) => {
            if (typeof e.detail !== 'undefined') {
                let table = $('.table');
                let table_tr = table.find('tr');
                const limit_tr = e.detail + 1;
                table_tr.each((index, value) => {
                    if (index >= limit_tr) {
                        $(value).remove();
                    }
                });
            }
        });
        request.fail((e) => {
            console.log(e);
        });
    }
    setInterval(checkMessage, 1000);
});