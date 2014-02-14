<?PHP
namespace Mailgun\Tests\Messages;

use Mailgun\Tests\MailgunTest;

class StandardMessageTest extends \Mailgun\Tests\MailgunTestCase{

	private $client;
	private $sampleDomain = "samples.mailgun.org";

	public function setUp(){ 
		$this->client = new MailgunTest("My-Super-Awesome-API-Key");
	}
	
	public function testSendMIMEMessage(){
		$customMime = "Received: by luna.mailgun.net with SMTP mgrt 8728174999085; Mon, 10 Jun 2013 09:50:58 +0000
					   Mime-Version: 1.0
					   Content-Type: text/plain; charset=\"ascii\"
					   Subject: This is the Subject!
					   From: Mailgun Testing <test@test.mailgun.com>
					   To: test@test.mailgun.com
					   Message-Id: <20130610095049.30790.4334@test.mailgun.com>
					   Content-Transfer-Encoding: 7bit
					   X-Mailgun-Sid: WyIxYTdhMyIsICJmaXplcmtoYW5AcXVhZG1zLmluIiwgImExOWQiXQ==
					   Date: Mon, 10 Jun 2013 09:50:58 +0000
					   Sender: test@test.mailgun.com

					   Mailgun is testing!";

		$envelopeFields = array('to' => 'test@test.mailgun.org');
		$result = $this->client->sendMessage("test.mailgun.org", $envelopeFields, $customMime);

		$requestBody = $result->http_request_body[0]->getPostFields();
		$requestUrl = $result->http_response_body;

		$this->assertEquals('https://api.mailgun.net/v2/test.mailgun.org/messages.mime', $requestUrl->getEffectiveUrl());
   		$this->assertEquals('test@test.mailgun.org', $requestBody->get('to'));
	}
	
	public function testSendMessage(){
		$message = array('to'      => 'test@test.mailgun.org', 
			             'from'    => 'sender@test.mailgun.org', 
			             'subject' => 'This is my test subject', 
			             'text'    => 'Testing!');

		$result = $this->client->sendMessage('test.mailgun.org', $message);

		$requestBody = $result->http_request_body[0]->getPostFields();
		$requestUrl = $result->http_response_body;

		$this->assertEquals('test@test.mailgun.org', $requestBody->get('to'));
		$this->assertEquals('sender@test.mailgun.org', $requestBody->get('from'));
		$this->assertEquals('This is my test subject', $requestBody->get('subject'));
		$this->assertEquals('Testing!', $requestBody->get('text'));
		$this->assertEquals('https://api.mailgun.net/v2/test.mailgun.org/messages', $requestUrl->getEffectiveUrl());
	}

	public function testAttachments(){
		$message = array('to'      => 'test@test.mailgun.org', 
						 'from'    => 'sender@test.mailgun.org', 
						 'subject' => 'This is my test subject', 
						 'text'    => 'Testing!');

		$attachments = array('attachment' => array('@tests/Mailgun/Tests/TestAssets/mailgun_icon.png'));

		$result = $this->client->sendMessage("test.mailgun.org", $message, $attachments);

		$requestBody = $result->http_request_body[0]->getPostFields();
		$requestFiles = $result->http_request_body[0]->getPostFiles();
		$requestUrl = $result->http_response_body;

		$this->assertEquals('test@test.mailgun.org', $requestBody->get('to'));
		$this->assertEquals('sender@test.mailgun.org', $requestBody->get('from'));
		$this->assertEquals('This is my test subject', $requestBody->get('subject'));
		$this->assertEquals('Testing!', $requestBody->get('text'));

		$file = $requestFiles["attachment"][0];
		$this->assertEquals('tests/Mailgun/Tests/TestAssets/mailgun_icon.png', $file->getFilename());
		$this->assertEquals('mailgun_icon.png', $file->getPostname());
		$this->assertEquals('attachment', $file->getFieldname());
		$this->assertEquals('image/png', $file->getContentType());

		$this->assertEquals('https://api.mailgun.net/v2/test.mailgun.org/messages', $requestUrl->getEffectiveUrl());
		
	}

	public function testComplexAttachment(){
		$message = array('to'      => 'test@test.mailgun.org', 
						 'from'    => 'sender@test.mailgun.org', 
						 'subject' => 'This is my test subject', 
						 'text'    => 'Testing!');

		$attachments = array('attachment' => array(
				  array('filePath' => '@tests/Mailgun/Tests/TestAssets/mailgun_icon.png', 
					    'remoteName' => 'test.png')));

		$result = $this->client->sendMessage("test.mailgun.org", $message, $attachments);

		$requestBody = $result->http_request_body[0]->getPostFields();
		$requestFiles = $result->http_request_body[0]->getPostFiles();
		$requestUrl = $result->http_response_body;

		$this->assertEquals('test@test.mailgun.org', $requestBody->get('to'));
		$this->assertEquals('sender@test.mailgun.org', $requestBody->get('from'));
		$this->assertEquals('This is my test subject', $requestBody->get('subject'));
		$this->assertEquals('Testing!', $requestBody->get('text'));

		$file = $requestFiles["attachment"][0];
		$this->assertEquals('tests/Mailgun/Tests/TestAssets/mailgun_icon.png', $file->getFilename());
		$this->assertEquals('test.png', $file->getPostname());
		$this->assertEquals('attachment', $file->getFieldname());
		$this->assertEquals('image/png', $file->getContentType());

		$this->assertEquals('https://api.mailgun.net/v2/test.mailgun.org/messages', $requestUrl->getEffectiveUrl());
		
	}

}
?>