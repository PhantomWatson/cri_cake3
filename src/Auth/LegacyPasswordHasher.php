<?php
namespace App\Auth;

use Cake\Auth\AbstractPasswordHasher;
use Cake\Core\Configure;

class LegacyPasswordHasher extends AbstractPasswordHasher
{

    public function hash($password)
    {
        $salt = Configure::read('Security.legacySalt');
        return sha1($salt.$password);
    }

    public function check($password, $hashedPassword)
    {
        return $this->hash($password) == $hashedPassword;
    }
}
