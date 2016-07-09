<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Worker;

class DatainputAuthController extends Controller
{

    //
    public function getLogin()
    {
        return view('auth.workerlogin');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);
        $credentials = $this->getCredentials($request);
        $worker_id = $this->attemptWorkerAuth($credentials);
        if ($worker_id) {
            Auth::guard('datainput')->loginUsingId($worker_id);
            return $this->handleUserWasAuthenticated($request);
        }

 /*       if (Auth::guard('datainput')->attempt($credentials, $request->has('remember'))) {
            return $this->handleUserWasAuthenticated($request);
        }*/

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.

        return $this->sendFailedLoginResponse($request);
    }

    /* Учитывая, что в таблицу workers пароли пользователей храняться в нехешерованном виде,
    проверяем вручную */

    protected function attemptWorkerAuth($credentials)
    {
        $worker = Worker::where('name', $credentials['name'])->where('password' , $credentials['password'])->first();
        return $worker ? $worker->id : 0;
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            'name' => 'required', 'password' => 'required',
        ]);
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  bool  $throttles
     * @return \Illuminate\Http\Response
     */
    protected function handleUserWasAuthenticated(Request $request)
    {
        if (method_exists($this, 'authenticated')) {
            return $this->authenticated($request, Auth::guard('datainput')->user());
        }

        return redirect()->intended('datainput');
    }

    /**
     * Get the failed login response instance.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        return redirect()->back()
            ->withInput($request->only('name', 'remember'))
            ->withErrors([
                'failedLogin' => $this->getFailedLoginMessage(),
            ]);
    }

    /**
     * Get the failed login message.
     *
     * @return string
     */
    protected function getFailedLoginMessage()
    {
        return Lang::has('auth.failed')
            ? Lang::get('auth.failed')
            : 'These credentials do not match our records.';
        //return 'Введенные имя пользователя и пароль не верны.';
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function getCredentials(Request $request)
    {
        return $request->only('name', 'password');
    }

    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLogout()
    {
        return $this->logout();
    }

    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        Auth::guard('datainput')->logout();
        return redirect('workerlogin');
    }

    /**
     * Get the guest middleware for the application.
     */
    public function guestMiddleware()
    {
        $guard = $this->getGuard();

        return $guard ? 'guest:'.$guard : 'guest';
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function loginUsername()
    {
        return property_exists($this, 'name') ? $this->username : 'email';
    }

}
