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
                // in future not necessary below condition
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
            $module = isset($routeNameArray[1])?$routeNameArray[1]:null;
            //$operation = isset($routeNameArray[2])?$routeNameArray[2]:null;
            $operation = removeSubString($routeName, $module);
            $operation = str_replace('.','',$operation);

            // get route
            $routeUri = removeSubString($route['uri'], '/{id}');

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
                        'is_permit' => 0
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
                            'is_permit' => 0
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


