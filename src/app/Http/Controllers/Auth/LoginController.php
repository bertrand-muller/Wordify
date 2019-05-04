<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        /*
         * Remove the socialite session variable if exists
         */

        \Session::forget(config('access.socialite_session_name'));

        $request->session()->flush();

        $request->session()->regenerate();

        return redirect('/');
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        $errors = [$this->username() => __('auth.failed')];

        if ($request->expectsJson()) {
            return response()->json($errors, 422);
        }

        return redirect()->back()
            ->withInput($request->only($this->username(), 'remember'))
            ->withErrors($errors);
    }

    public function loginFromGuest(Request $request){
        $this->logout($request);
        return $this->login($request);
    }

    public function updateProfile(Request $request){
        $errors = [];
        $name = filter_var($request->input('name'), FILTER_SANITIZE_SPECIAL_CHARS);
        $password = $request->input('password');
        $confirmPassword = $request->input('password_confirmation');
        $user = auth()->user();
        if($name != ""){
            $user->name = $name;
            $user->save();
        }
        if($password != ""){
           if($password == $confirmPassword){
               if(strlen($password) < 6){
                   $errors = [$this->username() => 'The password must be at least 6 characters.'];
               }else{
                   $user->password = bcrypt($password);
                   $user->save();
               }
           }else{
               $errors = [$this->username() => 'The password confirmation does not match.'];
           }
        }

        if ($request->hasFile('picture')) {

            $picture = $request->file('picture');
            $extension = $picture->getClientOriginalExtension();

            // Verify extension.
            if (!in_array($extension, ['jpeg', 'jpg', 'JPG', 'PNG', 'png'])) {
                return Response::create(['error' => 'Only the following formats are accepted to upload a picture: ".jpeg", ".jpg" ou ".png"'], 400);
            }

            // Verify size is less than 4Mo (4194304 octets).
            if ($picture->getClientSize() > 4194304) {
                return Response::create(['error' => 'The picture is too big. It should be less than 4Mo.'], 400);
            }

            if($user->image != 'guest.png') {
                unlink(public_path('/uploads/users/' . $user->image));
            }
            $filename = uniqid() . time() . '.' . $extension;
            Image::make($picture)->resize(32, 32)->save(public_path('/uploads/users/' . $filename));

            $user->image = $filename;
            $user->save();
        }

        return redirect()->back()
            ->withInput($request->only($this->username(), 'password_confirmation'))
            ->withErrors($errors);
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  mixed $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        $errors = [];

        if (config('auth.users.confirm_email') && !$user->confirmed) {
            $errors = [$this->username() => __('auth.notconfirmed', ['url' => route('confirm.send', [$user->email])])];
        }

        if (!$user->active) {
            $errors = [$this->username() => __('auth.active')];
        }

        if ($user->isGuest) {
            $errors = [$this->username() => __('auth.guest')];
        }

        if ($errors) {
            auth()->logout();  //logout

            return redirect()->back()
                ->withInput($request->only($this->username(), 'remember'))
                ->withErrors($errors);
        }

        return redirect()->intended($this->redirectPath());
    }
}
