<?php

namespace Application\FormFilter;

use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\InputFilter\Factory;
use Zend\Validator\StringLength;

/**
 * Class Message
 *
 * @package Application\FormFilter
 */
class Message
{
    /**
     * @var Factory
     */
    private $factory;

    /**
     * Message constructor.
     */
    public function __construct()
    {
        $this->factory = new Factory();
    }

    /**
     * @return \Zend\InputFilter\InputFilterInterface
     */
    public function getFilter()
    {
        $filter = $this->factory->createInputFilter([
            'message' => [
                'name' => 'message',
                'required' => true,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
                'validators' => [
                    [
                        'name' => StringLength::class,
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min' => 5,
                            'max' => 100,
                        ],
                    ],
                ],
            ],
        ]);
        return $filter;
    }
}