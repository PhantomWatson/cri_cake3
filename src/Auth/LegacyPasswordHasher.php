<?php
namespace App\Auth;

use Cake\Auth\AbstractPasswordHasher;

class LegacyPasswordHasher extends AbstractPasswordHasher
{

    public function check($password, $hashedPassword)
    {
        $salt = Configure::read('Security.legacySalt');
        $compare = sha1($salt.$password);
        return $compare == $hashedPassword;
    }
}
