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
