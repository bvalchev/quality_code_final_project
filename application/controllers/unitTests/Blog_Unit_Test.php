<?php

require_once APPPATH.'controllers/Blog_Controller.php';


class Blog_Unit_Test extends Blog_Controller{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('unit_test');
    }

    public function testBlogController(){
       $this->testBlogPostCreation();
       $this->testBlogPostGet();
       $this->testBlogPostUpdate();
       $this->testBlogPostDeletionWithoutId();
       $this->testBlogPostDelete();
    }

    private $testBlogPostId = -1;

    private function testBlogPostCreation(){
        $testName = 'Try to create blog post.';
        $postParams = json_encode([
            "blog_post_name" => "Test Blog Post",
            "blog_post_heading" => "I am test post",
            "blog_post_body" => "Custom test text"
        ]);
        $method = 'POST';
        $test = $this->index($method, $postParams);
        $expectedResult = '{"response":"'. INSERT_SUCCESSFUL_MESSAGE . '","code":' . SUCCESSFUL_REQUEST_CODE . '}';
        echo $this->unit->run($test, $expectedResult, $testName);
    }

    private function testBlogPostGet(){
        $testName = 'Try to get blog posts.';
        $postParams = json_encode([]);
        $method = 'GET';
        $result = $this->index($method, $postParams);
        $this->testBlogPostId = intval(json_decode($result, true)[0]["blog_id"]);
        $test = count($result);
        $expectedResult = 1;
        echo $this->unit->run($test, $expectedResult, $testName);
    }

    private function testBlogPostUpdate(){
        $testName = 'Try to update blog post.';
        $postParams = json_encode([
            "blog_id" => $this->testBlogPostId,
            "blog_post_name" => "Updated Test Blog Post",
            "blog_post_heading" => "I am updated test post",
            "blog_post_body" => "Updated Custom test text"
        ]);
        $method = 'POST';
        $test = $this->index($method, $postParams);
        $expectedResult = '{"response":"'. UPDATE_SUCCESSFUL_MESSAGE . '","code":' . SUCCESSFUL_REQUEST_CODE . '}';
        echo $this->unit->run($test, $expectedResult, $testName);
    }

    private function testBlogPostDeletionWithoutId(){
        $testName = 'Try to delete blog post without providing ID. Error message should appear.';
        $postParams = json_encode([]);
        $method = 'DELETE';
        $test = $this->index($method, $postParams);
        $expectedResult = false;
        echo $this->unit->run($test, $expectedResult, $testName);
    }

    private function testBlogPostDelete(){
        $testName = 'Try to delete blog post.';
        $postParams = json_encode([
            "blog_id" => $this->testBlogPostId
        ]);
        $method = 'DELETE';
        $test = $this->index($method, $postParams);
        $expectedResult = '{"response":"'. DELETE_SUCCESSFUL_MESSAGE . '","code":' . SUCCESSFUL_REQUEST_CODE . '}';
        echo $this->unit->run($test, $expectedResult, $testName);
    }
}