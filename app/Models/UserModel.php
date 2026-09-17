<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'username',
        'password_hash',
        'role',
        'is_first_login',
        'ref_id',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Find user by username
     */
    public function findByUsername(string $username)
    {
        return $this->where('username', $username)->first();
    }

    /**
     * Update user password and set is_first_login to 0
     */
    public function updatePassword(int $userId, string $newPassword): bool
    {
        return $this->update($userId, [
            'password_hash'  => password_hash($newPassword, PASSWORD_BCRYPT),
            'is_first_login' => 0,
        ]);
    }
}
