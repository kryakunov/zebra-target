<?php



require '../Token.php';

class Test
{
    public function getToken()
    {
       // return 'andrey';
        
        $token = Token::where('status', '=', 'free')->first();
        return $token->token;

    }
}