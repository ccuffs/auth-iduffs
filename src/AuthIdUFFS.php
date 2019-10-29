<?php

namespace CCUFFS\Auth;

/**
 * A sample class
 *
 * Use this section to define what this class is doing, the PHPDocumentator will use this
 * to automatically generate an API documentation using this information.
 *
 * @author Mateus Koppe
 * @author Fernando Bevilacqua <fernando.bevilacqua@uffs.edu.br>
 */
class AuthIdUFFS
{
    /**
     * Sample method 
     *
     * Always create a corresponding docblock for each method, describing what it is for,
     * this helps the phpdocumentator to properly generator the documentation
     *
     * @param string $param1 A string containing the parameter, do this for each parameter to the function, make sure to make it descriptive
     *
     * @return string
     */
    public function login($params)
    {
        $username = $params['user'];
        $password = $params['password'];

        $user_token = $this->getLoginToken($username, $password);

		if(!isset($user_token)) {
			return null;
        }
        
        $user_data = $this->getUserInPortal($username, $user_token);
        
		if (!$user_data) {
            return null;
        }
        
        return $user_data;
    }

    private function getServiceToken() {
        // TODO: checar sobre esse token
        return '';
    }

    public function getLoginToken($username, $password)
    {
        $data = '{
            "authId":"' . $this->getServiceToken() . '",
            "template":"",
            "stage":"DataStore1",
            "header":"Entre com seu IdUFFS",
            "callbacks": [
                {
                    "type":"NameCallback",
                    "output": [
                        {"name":"prompt","value":"IdUFFS ou CPF"}
                    ],
                    "input":
                    [
                        {"name":"IDToken1","value":"' . $username . '"}
                    ]
                },
                {
                    "type":"PasswordCallback",
                    "output": [
                        {"name":"prompt","value":"Senha"}
                    ],
                    "input": [
                        {"name":"IDToken2","value":"' . $password . '"}
                    ]
                }
            ]
        }';

        $response = $this->requestPost(
            'https://id.uffs.edu.br/id/json/authenticate?realm=/',
            json_decode($data)
        );

        $response = json_decode($response);

        if (!isset($response->tokenId)) {
            return null;
        }

        return $response->tokenId;
    }

    public function getUserInPortal($username, $user_token)
    {
        $userdata = $this->requestGet(
            "https://id.uffs.edu.br/id/json/users/$username",
            ['headers' => ["Cookie: iPlanetDirectoryPro=$user_token"]]
        );

        $userdata = json_decode($userdata);

        if (isset($userdata->code) && $userdata->code == 401) {
            return null;
        }

        if (isset($userdata->code) && $userdata->code == 403) {
            $matches = null;
            preg_match('/id=(.*?),ou=user/', $userdata->message, $matches);
            $username_test = $matches[1];
            return $this->getUserInPortal($username_test, $user_token);
        }

        $userdata->token_id = $user_token;
        $userdata->authenticated = true;

        return $this->formatIdUffsResult($userdata);
    }

    private function formatIdUffsResult($data)
    {
        return (object) [
            'username' => $data->username,
            'uid' => $data->uid[0],
            'email' => $data->mail[0],
            'pessoa_id' => $data->pessoa_id[0],
            'name' => $data->cn[0],
            'cpf' => $data->employeeNumber[0],
            'token_id' => $data->token_id,
            'authenticated' => $data->authenticated
        ];
    }

    private function generateCurlHandler($url, $debug = false)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        $caPathOrFile = \Composer\CaBundle\CaBundle::getSystemCaRootBundlePath();

        if (is_dir($caPathOrFile)) {
            curl_setopt($ch, CURLOPT_CAPATH, $caPathOrFile);
        } else {
            curl_setopt($ch, CURLOPT_CAINFO, $caPathOrFile);
        }

        if($debug) {
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_STDERR, fopen('./curl.log', 'w+'));
        }

        return $ch;
    }

    public function requestGet($url, $options = [])
    {
        $ch = $this->generateCurlHandler($url);

        curl_setopt($ch, CURLOPT_HTTPGET, true);

        if (isset($options['headers'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $options['headers']);
        }

        $result = curl_exec($ch);

        if(curl_errno($ch)){
            throw new \Error(curl_error($ch));
        }

        curl_close($ch);

        return $result;
    }


    public function requestPost($url, $data = [], $options = [])
    {
        $ch = $this->generateCurlHandler($url);
        $data = json_encode($data);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ));

        $result = curl_exec($ch);

        if(curl_errno($ch)){
            throw new \Error(curl_error($ch));
        }

        curl_close($ch);

        return $result;
    }
}
