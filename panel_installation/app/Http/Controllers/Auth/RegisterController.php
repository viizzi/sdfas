<?php

namespace Pterodactyl\Http\Controllers\Auth;

use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use Pterodactyl\Models\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Container\Container;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\Hashing\Hasher;
use Pterodactyl\Services\Users\UserCreationService;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Pterodactyl\Contracts\Repository\UserRepositoryInterface;

class RegisterController extends AbstractLoginController
{
    /**
     * @var \Illuminate\Contracts\View\Factory
     */
    private $view;

    protected AuthManager $auth;

    /**
     * @var \Pterodactyl\Services\Users\UserCreationService
     */
    protected $creationService;

    /**
     * RegisterController constructor.
     */
    public function __construct(
        ViewFactory $view,
        Hasher $hasher,
        UserRepositoryInterface $repository,
        UserCreationService $creationService
    ) {
        $this->view = $view;
        $this->hasher = $hasher;
        $this->repository = $repository;
        $this->creationService = $creationService;
        $this->auth = Container::getInstance()->make(AuthManager::class);
    }

    /**
     * Handle all incoming requests for the authentication routes and render the
     * base authentication view component. Vuejs will take over at this point and
     * turn the login area into a SPA.
     */
    public function index(): View
    {
        return $this->view->make('templates/auth.core');
    }

    /**
     * Handle a register request to the application.
     */
    public function register(Request $request): JsonResponse
    {
        $email = $request->input('email');
        $username = $request->input('username');
        $first_name = $request->input('first_name');
        $last_name = $request->input('last_name');
        $password = $request->input('password');

        $exist_email = DB::table('users')->where("email", "=", $email)->first();
        $exist_username = DB::table('users')->where("username", "=", $username)->first();

        if(!is_null($exist_email)){
            return new JsonResponse([
                'complete' => false,
                'message' => "There is already an account with that email!",
            ]);
        }else if(!is_null($exist_username)){
            return new JsonResponse([
                'complete' => false,
                'message' => "There is already an account with that username!",
            ]);
        }

        $user = [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'name_first' => $first_name,
            'name_last' => $last_name,
            'root_admin' => false
        ];

        $user = $this->creationService->handle($user);

        $this->auth->guard()->login($user, true);

        return new JsonResponse([
            'complete' => true,
            'message' => "",
        ]);
    }
}