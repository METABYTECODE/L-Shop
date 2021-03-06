<?php
declare(strict_types = 1);

namespace App\Http\Controllers\Admin\Control;

use App\Handlers\Admin\Control\Security\VisitHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Control\SaveSecurityRequest;
use App\Services\Auth\Permissions;
use App\Services\Notification\Notifications\Success;
use App\Services\Response\JsonResponse;
use App\Services\Response\Status;
use App\Services\Settings\Settings;
use function App\permission_middleware;

class SecurityController extends Controller
{
    public function __construct()
    {
        $this->middleware(permission_middleware(Permissions::ADMIN_CONTROL_SECURITY_ACCESS));
    }

    public function render(VisitHandler $handler): JsonResponse
    {
        return new JsonResponse(Status::SUCCESS, $handler->handle());
    }

    public function save(SaveSecurityRequest $request, Settings $settings): JsonResponse
    {
        $settings->setArray([
            'system' => [
                'security' => [
                    'captcha' => [
                        'enabled' => (bool)$request->get('captcha_enabled'),
                        'recaptcha' => [
                            'public_key' => $request->get('recaptcha_public_key'),
                            'secret_key' => $request->get('recaptcha_secret_key')
                        ]
                    ]
                ],
            ],
            'auth' => [
                'reset_password' => [
                    'enabled' => (bool)$request->get('reset_password_enabled')
                ],
                'change_password' => [
                    'enabled' => (bool)$request->get('change_password_enabled')
                ],
            ]
        ]);
        $settings->save();

        return (new JsonResponse(Status::SUCCESS))
            ->addNotification(new Success(__('common.changed')));
    }
}
