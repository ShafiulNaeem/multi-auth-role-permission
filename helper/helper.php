<?php


if ( ! function_exists('sendResponse') ){
    /**
     * @param $message
     * @param $result
     * @param int $code
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    function sendResponse($message, $result, int $code = 200) {
        $response = [
            'code'    => $code,
            'success' => true,
            'message' => $message,
            'data'    => $result,
            'errors'  => []
        ];

        return response($response, $code);
    }
}


if( ! function_exists('sendError') ){
    /**
     * @param $error
     * @param array $errorMessages
     * @param int $code
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    function sendError($error, array $errorMessages = [], int $code = 404) {
        $response = [
            'code'    => $code,
            'success' => false,
            'message' => $error,
            'data'    => [],
            'errors'  => $errorMessages,
        ];

        return response($response, $code);
    }
}

if ( ! function_exists('routePermission') ){
    /**
     * @param $current_url,$role_id,$user_id
     * @return boolean
     */
    function routePermission($current_url,$role_id,$user_id){

        $permissions = \Illuminate\Support\Facades\DB::table('role_permission_modifications')
            ->where('role_permission_modifications.role_id',$role_id)
            ->where('role_permission_modifications.auth_user_id',$user_id)
            ->where('role_permission_modifications.is_permit',1)
            ->pluck('route')->toArray();
        $access = false;
        if (count($permissions) > 0){
            foreach ($permissions as $route){
                /** in future not necessary below condition **/
                if (strpos($route,'list/global') !== false){
                    $route =  strstr($route, '/global', true);
                }
                if(strpos($current_url,$route) !== false){
                    $access = true;
                    break;
                }
            }
        }
        return $access;
    }
}
