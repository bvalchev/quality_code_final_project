<?php

require_once APPPATH.'controllers/Authentication_Controller.php';


class Authentication_Unit_Test extends Authentication_Controller{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('unit_test');
    }

    public function testAuthenticationController(){
        $this->testUserCreationWithoutUsername();
        $this->testBasicUserCreation();
        $this->testCreatingUserWithExistingName();
        $this->testLoginForNotExistingUser();
        $this->testLoginWithIncorrectPassword();
        $this->testPasswordUpdateForNotExistingUser();
        $this->testPasswordUpdateWithWrongOldPassword();
        $this->testPasswordUpdate();
        $this->testDeleteUserValidation();
        $this->testDeleteUserWithoutUsernameProvided();
        $this->testDeleteUser();  
    }

    private function testUserCreationWithoutUsername(){
        $testName = 'Try to create username, without username.';
        $postParams = json_encode([
            "password" => "qwerty",
            "position" => "Administrator"
        ]);
        $test = $this->insertUser($postParams);
        $expectedResult = '{"response":"'. VALIDATION_ERROR_MESSAGE . '","code":'. BAD_REQUEST_ERROR_CODE. '}';
        echo $this->unit->run($test, $expectedResult, $testName);
    }

    private function testBasicUserCreation(){
        $testName = 'Basic User creation';
        $postParams = json_encode([
            "username" => "testUser",
            "password" => "qwerty",
            "position" => "Administrator"
        ]);
        $test = $this->insertUser($postParams);
        $expectedResult = '{"response":"'. INSERT_SUCCESSFUL_MESSAGE . '","code":' . SUCCESSFUL_REQUEST_CODE . '}';
        echo $this->unit->run($test, $expectedResult, $testName);
    }

    private function testCreatingUserWithExistingName(){
        $testName = 'Try creating user with already existing name';
        $postParams = json_encode([
            "username" => "testUser",
            "password" => "qwerty",
            "position" => "Administrator"
        ]);
        $test = $this->insertUser($postParams);
        $expectedResult = '{"response":"'. USERNAME_TAKEN_MESSAGE . '","code":'. BAD_REQUEST_ERROR_CODE. '}';
        echo $this->unit->run($test, $expectedResult, $testName);
    }

    private function testLoginForNotExistingUser(){
        $testName = 'Test login';
        $postParams = json_encode([
            "username" => "testUser1",
            "password" => "qwerty"
        ]);
        $test = $this->login($postParams);
        $expectedResult = '{"response":"'. USER_NOT_FOUND . '","code":'. BAD_REQUEST_ERROR_CODE. '}';
        echo $this->unit->run($test, $expectedResult, $testName);
    }

    private function testLoginWithIncorrectPassword(){
        $testName = 'Test login';
        $postParams = json_encode([
            "username" => "testUser",
            "password" => "qwerty123"
        ]);
        $test = $this->login($postParams);
        $expectedResult = '{"response":"'. INVALID_PASSWORD_MESSAGE . '","code":'. BAD_REQUEST_ERROR_CODE. '}';
        echo $this->unit->run($test, $expectedResult, $testName);
    }

    private function testPasswordUpdateForNotExistingUser(){
        $testName = 'Test updating user password for non-existing user. Validation error message should appear. ';
        $postParams = json_encode([
            "username" => "testUser1",
            "password" => "qwerty",
            "newPassword" => "asdfg",
            "position" => "Administrator"
        ]);
        $test = $this->updatePassword($postParams);
        $expectedResult = '{"response":"'. USER_NOT_FOUND . '","code":' . BAD_REQUEST_ERROR_CODE . '}';
        echo $this->unit->run($test, $expectedResult, $testName);
    }

    private function testPasswordUpdateWithWrongOldPassword(){
        $testName = 'Test updating user password with incorect old password provided. Validation error message should appear. ';
        $postParams = json_encode([
            "username" => "testUser",
            "password" => "qwerty123",
            "newPassword" => "asdfg",
            "position" => "Administrator"
        ]);
        $test = $this->updatePassword($postParams);
        $expectedResult = '{"response":"'. INVALID_PASSWORD_MESSAGE . '","code":' . BAD_REQUEST_ERROR_CODE . '}';
        echo $this->unit->run($test, $expectedResult, $testName);
    }


    private function testPasswordUpdate(){
        $testName = 'Test updating user password. Logout should happen';
        $postParams = json_encode([
            "username" => "testUser",
            "password" => "qwerty",
            "newPassword" => "asdfg123",
            "position" => "Administrator"
        ]);
        $test = $this->updatePassword($postParams);
        $expectedResult = '{"response":"'. LOGGED_OUT_MESSAGE . '","code":' . SUCCESSFUL_REQUEST_CODE . '}';
        echo $this->unit->run($test, $expectedResult, $testName);
    }

    private function testDeleteUserValidation(){
        $testName = 'Test deleting user, which is not existing. Validation error message should appear';
        $postParams = json_encode([
            "username" => "testUser1"
        ]);
        $test = $this->deleteUser($postParams);
        $expectedResult = '{"response":"'. USER_NOT_FOUND_MESSAGE . '","code":'. BAD_REQUEST_ERROR_CODE. '}';
        echo $this->unit->run($test, $expectedResult, $testName); 
    }

    private function testDeleteUserWithoutUsernameProvided(){
        $testName = 'Test deleting user, without username provided. Validation error message should appear';
        $postParams = json_encode([
        ]);
        $test = $this->deleteUser($postParams);
        $expectedResult = false;
        echo $this->unit->run($test, $expectedResult, $testName); 
    }

    private function testDeleteUser(){
        $testName = 'Test deleting user';
        $postParams = json_encode([
            "username" => "testUser"
        ]);
        $test = $this->deleteUser($postParams);
        $expectedResult = '{"response":"'. DELETE_SUCCESSFUL_MESSAGE . '","code":' . SUCCESSFUL_REQUEST_CODE . '}';
        echo $this->unit->run($test, $expectedResult, $testName); 
    }
}