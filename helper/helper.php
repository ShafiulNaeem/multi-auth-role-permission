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
                if( matchTwoStringPattern($current_url,$route) ){
                    $access = true;
                    break;
                }
            }
        }
        return $access;
    }
}


if ( ! function_exists('matchTwoStringPattern') ){
    function matchTwoStringPattern($stringOne, $stringTwo): bool
    {
        $stringOne = strtolower($stringOne);
        $stringTwo = strtolower($stringTwo);

        $stringOneArray = explode('/', $stringOne);
        $stringTwoArray = explode('/', $stringTwo);

        if ( count($stringOneArray) != count($stringTwoArray) ) return false;

        foreach ($stringOneArray as $index => $stringOneItem) {
            $match = preg_match('/{.*}/', $stringOneItem);
            if ( $match ){
                $stringOneArray[$index] = $stringTwoArray[$index];
            }

            if ( str_contains($stringOneItem, '?') || str_contains($stringTwoArray[$index], '?') ){
                if ( str_contains($stringOneArray[$index], '?') ){
                    $stringOneArray[$index] = strstr($stringOneArray[$index], '?', true);
                }

                if ( str_contains($stringTwoArray[$index], '?') ){
                    $stringTwoArray[$index] = strstr($stringTwoArray[$index], '?', true);
                }
            }

            if ($stringOneArray[$index] != $stringTwoArray[$index]) return false;
        }

        $strOneNew = implode('/', $stringOneArray);
        $strTwoNew = implode('/', $stringTwoArray);

        return $strOneNew == $strTwoNew;
    }
}


if ( ! function_exists('permission_data') ){
    /**
     * @param $model,$data
     * @return $paginate,
     */
    function permission_data($guard){
        return role_permission_array_from_route($guard);
    }
}
if ( ! function_exists('role_permission_array_from_route') ){
    function role_permission_array_from_route($guard): array
    {
        $guard = 'permission:'.$guard;
        $routes = routeFilter('middleware', $guard);

        $permissions = [];

        foreach ($routes as $index => $route) {
            // get name
            $routeName = $route['name'];
            $routeNameArray = explode('.', $routeName);
            $module = isset($routeNameArray[0])?$routeNameArray[0]:null;
            //$operation = isset($routeNameArray[2])?$routeNameArray[2]:null;
            $operation = removeSubString($routeName, $module);
            $operation = str_replace('.',' ',$operation);

            // get route
//            $routeUri = removeSubString($route['uri'], '/{id}');
            $routeUri = $route['uri'];

            // check if module exist
            if ( searchInMultidimensionalArray($permissions, 'module', $module) ){
                $geModuleIndex = array_search($module, array_column($permissions, 'module'));
                // check if operation exist
                if ( ! searchInMultidimensionalArray($permissions[$geModuleIndex]['permission'], 'operation', $operation) ){
                    $permissions[$geModuleIndex]['permission'][] = [
                        'auth_guard_id' => 0,
                        'role_id' => 0,
                        'auth_user_id' => 0,
                        'module' => $module,
                        'operation' => $operation,
                        'route' => $routeUri,
                        'is_permit' => 0,
                        'route_name' => $routeName,
                        'method' => getUsualMethodName($route['methods'])
                    ];
                }
            } else {
                $permissions[] = [
                    'module' => $module,
                    'permission' => [
                        [
                            'auth_guard_id' => 0,
                            'role_id' => 0,
                            'auth_user_id' => 0,
                            'module' => $module,
                            'operation' => $operation,
                            'route' => $routeUri,
                            'is_permit' => 0,
                            'route_name' => $routeName,
                            'method' => getUsualMethodName($route['methods'])
                        ],
                    ]
                ];
            }
        }

        return $permissions;
    }
}

if ( ! function_exists('routeFilter') ){
    function routeFilter($filterKey, $value): array
    {
        $routes = getAllRegisteredRoutes();
        return array_filter($routes, function ($item) use ($filterKey, $value) {
            if ( isset($item[$filterKey]) ){
                if ( is_array($item[$filterKey]) ){
                    return in_array($value, $item[$filterKey]);
                } else {
                    return $item[$filterKey] == $value;
                }
            }
            return false;
        });
    }
}
if ( ! function_exists('getAllRegisteredRoutes') ){
    function getAllRegisteredRoutes(): array
    {
        $routes = \Illuminate\Support\Facades\Route::getRoutes();
        $routeCollection = [];
        foreach ($routes as $route) {
            $routeCollection[] = [
                'name' => $route->getName(),
                'uri' => $route->uri(),
                'methods' => $route->methods(),
                'action' => $route->getActionName(),
                'middleware' => $route->middleware(),
            ];
        }

        return $routeCollection;
    }
}
if ( ! function_exists('removeSubString') ){
    function removeSubString($string, $subString)
    {
        return str_replace($subString, '', $string);
    }
}
if ( ! function_exists('searchInMultidimensionalArray') ){
    function searchInMultidimensionalArray($array, $key, $value)
    {
        $results = array();

        if (is_array($array)) {
            if (isset($array[$key]) && $array[$key] == $value) {
                $results[] = $array;
            }

            foreach ($array as $subarray) {
                $results = array_merge($results, searchInMultidimensionalArray($subarray, $key, $value));
            }
        }

        return $results;
    }
}
if ( ! function_exists('permission_db_data_format') ){
    /**
     * @param $data
     * @return Array,
     */
    function permission_db_data_format($data,$guard){
        $modules = array();
        $result = array();
        $permission_data = permission_data($guard);

        foreach ($permission_data as $per=>$permission_datum){
            $array = array();
            if (count($permission_datum['permission']) > 0){
                foreach ($permission_datum['permission'] as $pd=>$value){
                    $auth_guard_id = 0;
                    $role_id = 0;
                    $user_id = 0;
                    $is_permit = 0;

                    if (count($data) > 0){
                        $auth_guard_id = $data[0]['auth_guard_id'];
                        $role_id = $data[0]['role_id'];
                        $user_id = $data[0]['auth_user_id'];

                        $search = array_search($value['route'], array_column($data, 'route'));
                        if (!is_bool($search)){
                            $is_permit = $data[$search]['is_permit'];
                        }
                    }

                    $array[] = [
                        'auth_guard_id'=> (int) $auth_guard_id,
                        'role_id'=> (int) $role_id,
                        'auth_user_id'=> (int) $user_id,
                        'module'=> $value['module'],
                        'operation'=> $value['operation'],
                        'route'=> $value['route'],
                        'is_permit'=> (int) $is_permit,
                        'route_name' => $value['route_name'],
                        'method' => $value['method']
                    ];

                }
            }

            $result[] = [
                'module' => $permission_datum['module'],
                'permission' => $array
            ];
        }
        return $result;
    }
}

if ( ! function_exists('permission_db_data_format_v_two') ){
    /**
     * @param $data
     * @return Array,
     */
    function permission_db_data_format_v_two($data,$guard){
        $modules = array();
        $result = array();
        $permission_data = permission_data($guard);

        foreach ($permission_data as $per=>$permission_datum){
            $array = array();
            if (count($permission_datum['permission']) > 0){
                foreach ($permission_datum['permission'] as $pd=>$value){
                    $auth_guard_id = 0;
                    $role_id = 0;
                    $user_id = 0;
                    $is_permit = 0;

                    if ($data->count() > 0){
                        $auth_guard_id = $data[0]->auth_guard_id;
                        $role_id = $data[0]->role_id;
                        $user_id = $data[0]->auth_user_id;

                        $search = array_search($value['route'], array_column($data, 'route'));
                        if (!is_bool($search)){
                            $is_permit = $data[$search]['is_permit'];
                        }
                    }

                    $array[] = [
                        'auth_guard_id'=> (int) $auth_guard_id,
                        'role_id'=> (int) $role_id,
                        'auth_user_id'=> (int) $user_id,
                        'module'=> $value['module'],
                        'operation'=> $value['operation'],
                        'route'=> $value['route'],
                        'is_permit'=> (int) $is_permit
                    ];

                }
            }

            $result[] = [
                'module' => $permission_datum['module'],
                'permission' => $array
            ];
        }
        return $result;
    }
}

if ( ! function_exists('permission_data_format') ){
    /**
     * @param $data,$role_id
     * @return Array,
     */
    function permission_data_format($data,$auth_guard_id,$role_id,$user_id){
        $permissions = array();
        foreach ($data as $key=>$value){
            foreach ($value['permission'] as $item){
                $item['auth_guard_id'] = $auth_guard_id;
                $item['role_id'] = $role_id;
                $item['auth_user_id'] = $user_id;
                $permissions[] = $item;
            }
        }
        return $permissions;
    }
}

if ( ! function_exists('page_limit') ){
    /**
     * @param $params
     * @return int|mixed $paginate,
     */
    function page_limit($params){
        $paginate = 10;
        if (array_key_exists('page_limit',$params)){
            if ($params['page_limit']){
                $paginate = $params['page_limit'];
            }
        }
        return $paginate;
    }
}

if ( ! function_exists('getUsualMethodName') ){
    function getUsualMethodName($methods): string
    {
        $methods = array_map('strtolower', $methods);

        if ( in_array('get', $methods) ) return 'GET';
        if ( in_array('post', $methods) ) return 'POST';
        if ( in_array('put', $methods) ) return 'PUT';
        if ( in_array('patch', $methods) ) return 'PATCH';
        if ( in_array('delete', $methods) ) return 'DELETE';
        if ( in_array('options', $methods) ) return 'OPTIONS';
        if ( in_array('head', $methods) ) return 'HEAD';
        if ( in_array('trace', $methods) ) return 'TRACE';
        if ( in_array('connect', $methods) ) return 'CONNECT';
        return '';
    }
}
