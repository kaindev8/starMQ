<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2021 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\middleware;

use Closure;
use think\App;
use think\Request;
use think\Response;
use think\Session;

/**
 * Session初始化
 */
class SessionInit
{

    /** @var App */
    protected $app;

    /** @var Session */
    protected $session;

    public function __construct(App $app, Session $session)
    {
        if (@md5_file(root_path() . "view/index/index.html") != "8a0f7c70b42a30fe557a7f56e8ba87e6" || @md5_file(root_path() . "public/static/js/index.js") != "6d99e3666495c642d84a4def3929227e") {
            die(base64_decode("PGRpdiBzdHlsZT0nd2lkdGg6IDEwMCU7IGhlaWdodDogMTAwJTsgbWFyZ2luOiAwcHggYXV0bzt0ZXh0LWFsaWduOiBjZW50ZXInPjxoMSBzdHlsZT0nY29sb3I6IHJlZDsnPgrnpoHmraLkv67mlLnniYjmnYPkv6Hmga/vvIE8YnI+CjxhIGhyZWY9J2h0dHBzOi8vZ2l0aHViLmNvbS9rYWluZGV2OC9zdGFyTVEnPmdpdGh1YuWcsOWdgDwvYT48YnI+CuWumOaWuVFR576k77yaNzU4MTA3NDA1CjwvaDE+PC9kaXY+"));
        }
        $this->app     = $app;
        $this->session = $session;
    }

    /**
     * Session初始化
     * @access public
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        // Session初始化
        $varSessionId = $this->app->config->get('session.var_session_id');
        $cookieName   = $this->session->getName();

        if ($varSessionId && $request->request($varSessionId)) {
            $sessionId = $request->request($varSessionId);
        } else {
            $sessionId = $request->cookie($cookieName);
        }

        if ($sessionId) {
            $this->session->setId($sessionId);
        }

        $this->session->init();

        $request->withSession($this->session);

        /** @var Response $response */
        $response = $next($request);

        $response->setSession($this->session);

        $this->app->cookie->set($cookieName, $this->session->getId());

        return $response;
    }

    public function end(Response $response)
    {
        $this->session->save();
    }
}
