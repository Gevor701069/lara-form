<?php
namespace LaraForm\Middleware;

use Closure;
use Illuminate\Http\Request;
use LaraForm\FormProtection;

class LaraFormMiddleware
{

    public function handle(Request $request, Closure $next)
    {
        $isValidate = true;

        if ($request->method() == 'GET') {
            $isValidate = false;
        }

        if ($isValidate) {
            $formProtection = new FormProtection();
            $validate = $formProtection->validate($request->all());
            if($validate === false) {
                return redirect()->back();
            }
        }

        return $next($request);
    }
}
