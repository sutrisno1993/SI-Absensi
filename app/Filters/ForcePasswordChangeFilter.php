<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ForcePasswordChangeFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        if ($session->get('logged_in') && $session->get('role') === 'walas' && (int)$session->get('is_first_login') === 1) {
            return redirect()->to('/auth/ganti-password')->with('warning', 'Demi keamanan, Anda wajib mengganti password akun pada saat login pertama kali.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
