<?php

include_once 'pest/PestJSON.php';

function rest_error_as_string($error) {
	$result = '';
	if (is_array($error))
	{
		foreach ($error as $entry)
		{
			if ($result)
				$result .= ', '.rest_error_as_string($entry);
			else 
				$result = rest_error_as_string($entry);
		}
	}
	else
	{
		$result = (string) $error;
	}
	return htmlspecialchars($result);
}

class BaseRestErrorResponse extends Exception {
	function __construct($data, $message = 'Rest Error Response')
	{
		$this->data = $data;
		parent::__construct($message);
	}
}

class ComplexRestErrorResponse extends BaseRestErrorResponse {
}

class SimpleRestErrorResponse extends BaseRestErrorResponse {
}

class RestClient {
	
	function __construct($root_url, $api_key, $api_password)
	{
		$this->pest = new PestJSON($root_url);
		$this->pest->setupAuth($api_key, $api_password);
		$this->empty = array(""=>NULL);
	}
	
	function Signup($args) {
		try
		{
			$result = $this->pest->post('customers/', $args);
		}
		catch (Pest_BadRequest $e)
		{
			$this->_ThrowErrorResponse($e);
		}
	}

	function UpdateCustomer($customer_code, $args) {
		try
		{
			$url = sprintf('customers/%s/', urlencode($customer_code));
			$result = $this->pest->patch($url, $args);
		}
		catch (Pest_BadRequest $e)
		{
			$this->_ThrowErrorResponse($e);
		}
	}
	
	function CloseCustomer($customer_code)
	{
		try
		{
			$url = sprintf('customers/%s/', urlencode($customer_code));
			$result = $this->pest->delete($url, $this->empty);
		}
		catch (Pest_BadRequest $e)
		{
			$this->_ThrowErrorResponse($e);
		}
	}
	
	function LoginCustomerByCode($customer_code) {
		try
		{
			$url = sprintf('customers/%s/sessions/', urlencode($customer_code));
			$result = $this->pest->post($url, $this->empty);
			if (isset($result["login_url"]))
			{
				return $result;
			}
			else throw new Exception("Login failed - unexpected response from server");
		}
		catch (Pest_BadRequest $e)
		{
			$this->_ThrowErrorResponse($e);
		}
	}
	
	function Login($username, $password) {
		try 
		{
			$result = $this->pest->post('sessions/', 
					array(
						'username' => $username,
						'password' => $password
					)
				);
			if (isset($result["login_url"]))
			{
				return $result;
			}
			else throw new Exception("Login failed - unexpected response from server");
		}
		catch (Pest_BadRequest $e)
		{
			$this->_ThrowErrorResponse($e);
		}
	}
	
	function GetPurchaseRequestDetails($purchase_request_key)
	{
		try
		{
			$url = sprintf('purchaserequests/%s/', urlencode($purchase_request_key));
			return $this->pest->get($url);
		}
		catch (Pest_BadRequest $e)
		{
			$this->_ThrowErrorResponse($e);
		}
	}
	
	function ApprovePurchaseRequest($purchase_request_key, $package_definition_code, $purchase_code)
	{
		return $this->RespondToPurchaseRequest($purchase_request_key, $package_definition_code, $purchase_code, 'APPROVED');
	}

	function DenyPurchaseRequest($purchase_request_key, $package_definition_code, $purchase_code)
	{
		return $this->RespondToPurchaseRequest($purchase_request_key, $package_definition_code, $purchase_code, 'DENIED');
	}
	
	function RespondToPurchaseRequest($purchase_request_key, $package_definition_code, $purchase_code, $status_code)
	{
		try
		{
			$url = sprintf('purchaserequests/%s/', urlencode($purchase_request_key));
			$args['package_definition'] = $package_definition_code;
			$args['purchase_code'] = $purchase_code;
			$args['status_code'] = $status_code;
			return $this->pest->put($url, $args);
		}
		catch (Pest_BadRequest $e)
		{
			$this->_ThrowErrorResponse($e);
		}
	}
	
	function GetCancellationRequestDetails($cancellation_request_key)
	{
		try
		{
			$url = sprintf('cancellationrequests/%s/', urlencode($cancellation_request_key));
			return $this->pest->get($url);
		}
		catch (Pest_BadRequest $e)
		{
			$this->_ThrowErrorResponse($e);
		}
	}
	
	function ApproveCancellation($cancellation_request_key)
	{
		return $this->RespondToCancellationRequest($cancellation_request_key, 'APPROVED');
	}

	function DenyCancellation($purchase_request_key, $package_definition_code, $purchase_code)
	{
		return $this->RespondToCancellationRequest($cancellation_request_key, 'DENIED');
	}

	function RespondToCancellationRequest($cancellation_request_key, $status_code)
	{
		try
		{
			$url = sprintf('cancellationrequests/%s/', urlencode($cancellation_request_key));
			$args['status_code'] = $status_code;
			return $this->pest->put($url, $args);
		}
		catch (Pest_BadRequest $e)
		{
			$this->_ThrowErrorResponse($e);
		}
	}
	
	function _ThrowErrorResponse($e)
	{
		$result = json_decode($e->getMessage());
		if ($result == NULL)
		{
			throw $e;
		}
		if (count($result) == 1)
		{
			if (isset($result->detail))
			{
				throw new SimpleRestErrorResponse($result, $result->detail);
			}
			if (isset($result->non_field_errors))
			{
				throw new SimpleRestErrorResponse($result, $result->non_field_errors[0]);
			}
		}
		throw new ComplexRestErrorResponse($result);
	}	
}

?>