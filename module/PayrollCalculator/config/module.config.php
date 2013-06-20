<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'PayrollCalculator\Controller\Console' => 'PayrollCalculator\Controller\ConsoleController',

        ),
    ),
	'console' => array(
        'router' => array(
            'routes' => array(
                'outputpaydays' => array(
                    'options' => array(
                        'route'    => 'outputpaydays <fileName>',
                        'defaults' => array(
                            'controller' => 'PayrollCalculator\Controller\Console',
                            'action'     => 'outputpaydays'
                        ),
                    ),
                ),
            ),
        ),
    ),
);