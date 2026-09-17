<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        if (! $session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        $userRole = $session->get('role');

        if (! empty($arguments)) {
            $allowedRoles = [];
            foreach ($arguments as $arg) {
                $allowedRoles = array_merge($allowedRoles, array_map('trim', explode(',', $arg)));
            }

            if (! in_array($userRole, $allowedRoles)) {
                // Arahkan ke dashboard yang sesuai dengan peran user
                switch ($userRole) {
                    case 'admin':
                        return redirect()->to('/admin')->with('error', 'Anda tidak memiliki hak akses ke halaman tersebut.');
                    case 'walas':
                        return redirect()->to('/walas')->with('error', 'Anda tidak memiliki hak akses ke halaman tersebut.');
                    case 'guru_piket':
                        return redirect()->to('/piket')->with('error', 'Anda tidak memiliki hak akses ke halaman tersebut.');
                    case 'pj_kelas':
                        return redirect()->to('/pj')->with('error', 'Anda tidak memiliki hak akses ke halaman tersebut.');
                    default:
                        return redirect()->to('/auth/login');
                }
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
