<?php

namespace App\Http\Middleware;

use App\ERC20;
use app\Libraries\Traits\Cache\CacheKeyTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TokenType
{
    use CacheKeyTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($request->isMethod('OPTIONS')){
            $response_obj = response('', 200);
        }else {

            $token_type = $request->header('token-type', 'base'); // base|token
            $token_contract_address = $request->header('token-address', 'fe59cbc1704e89a698571413a81f0de9d8f00c69'); // ink
            //if($token_type != 'token'){
            if ($token_type == 'token') {
                // token
                if(is_null($erc20_obj = Cache::get($this->getERC20ObjCacheKey($token_contract_address)))) {
                    $erc20_obj = (new ERC20)->getERC20ByAddressHex($token_contract_address);

                    Cache::put($this->getERC20ObjCacheKey($token_contract_address), $erc20_obj, 1440*365);
                }
                $request->request->add(['erc20_obj' => $erc20_obj]);
                goto next_request;
            }
            // qtum

            next_request:
            $response_obj = $next($request);
        }

        $this->setCorsHeaders($request, $response_obj);

        return $response_obj;
    }


    // cors
    protected $settings = array(
        'origin' => '*',    // Wide Open!
        'allowMethods' => 'GET,HEAD,PUT,POST,DELETE,PATCH,OPTIONS',
    );
    protected function setOrigin($req, $rsp) {
        $origin = $this->settings['origin'];
        if (is_callable($origin)) {
            // Call origin callback with request origin
            $origin = call_user_func($origin,
                $req->header("Origin")
            );
        }
        $rsp->header('Access-Control-Allow-Origin', $origin);
    }
    protected function setExposeHeaders($req, $rsp) {
        if (isset($this->settings['exposeHeaders'])) {
            $exposeHeaders = $this->settings['exposeHeaders'];
            if (is_array($exposeHeaders)) {
                $exposeHeaders = implode(", ", $exposeHeaders);
            }

            $rsp->header('Access-Control-Expose-Headers', $exposeHeaders);
        }
    }
    protected function setMaxAge($req, $rsp) {
        if (isset($this->settings['maxAge'])) {
            $rsp->header('Access-Control-Max-Age', $this->settings['maxAge']);
        }
    }
    protected function setAllowCredentials($req, $rsp) {
        if (isset($this->settings['allowCredentials']) && $this->settings['allowCredentials'] === True) {
            $rsp->header('Access-Control-Allow-Credentials', 'true');
        }
    }
    protected function setAllowMethods($req, $rsp) {
        if (isset($this->settings['allowMethods'])) {
            $allowMethods = $this->settings['allowMethods'];
            if (is_array($allowMethods)) {
                $allowMethods = implode(", ", $allowMethods);
            }

            $rsp->header('Access-Control-Allow-Methods', $allowMethods);
        }
    }
    protected function setAllowHeaders($req, $rsp) {
        if (isset($this->settings['allowHeaders'])) {
            $allowHeaders = $this->settings['allowHeaders'];
            if (is_array($allowHeaders)) {
                $allowHeaders = implode(", ", $allowHeaders);
            }
        }
        else {  // Otherwise, use request headers
            $allowHeaders = $req->header("Access-Control-Request-Headers");
        }
        if (isset($allowHeaders)) {
            $rsp->header('Access-Control-Allow-Headers', $allowHeaders);
        }
    }

    protected function setCorsHeaders($req, $rsp) {
        // http://www.html5rocks.com/static/images/cors_server_flowchart.png
        // Pre-flight
        if ($req->isMethod('OPTIONS')) {
            $this->setOrigin($req, $rsp);
            $this->setMaxAge($req, $rsp);
            $this->setAllowCredentials($req, $rsp);
            $this->setAllowMethods($req, $rsp);
            $this->setAllowHeaders($req, $rsp);
        }
        else {
            $this->setOrigin($req, $rsp);
            $this->setExposeHeaders($req, $rsp);
            $this->setAllowCredentials($req, $rsp);
        }
    }
}
