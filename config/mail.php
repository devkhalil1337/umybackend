<?php
return array(
    'driver' => 'smtp',
    'host' => 'mail.umyocards.com',
    'port' => 465,
    'from' => array('address' => 'verification@umyocards.com', 'name' => 'verification@umyocards.com'),
    'encryption' => 'ssl',
    'username' => 'verification@umyocards.com',
    'password' => '>(w[A"zsl,^3O',
    'sendmail' => '/usr/sbin/sendmail -bs',
    'pretend' => true,
);
// mail