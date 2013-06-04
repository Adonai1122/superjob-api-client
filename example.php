<?
/**
*	����� ������� ������ ������ � Superjob.ru API
*	��������������� ����� ������� ��������, ��������,
*	� ��� �� ����� �������� � ���������� ����� OAuth
*
*	��� �������� ������������ JSON
*
*	��� ����, ����� ������� ������ � OAuth, 
*	��������� ��������� � ����� config.php
**/
session_start();
?>
<!DOCTYPE html>
<html>
<head>
	<title>API SuperJob.ru Example</title>
	<link rel="stylesheet" href="css/normalize.css">
	<link rel="stylesheet" href="css/code.css">
	<link rel="stylesheet" href="css/main.css">
</head>
<body>
<?php

include_once('class.SuperjobAPIClient.php');
function process_array($array)
{
	if ($array = json_decode($array, true))
	{
		array_walk_recursive($array, function(&$value,$key){
   			$value=iconv("UTF-8","CP1251",$value);
		});
	}
	return $array;

}
try 
{
	$APIClient = SuperjobAPIClient::instance();
?>
<div class="g_layout">
	<div class="g_layout_wrapper">
<h1>API Superjob.ru. �������</h1>
<h2>������ ��������: GetClientsList</h2>
<div class="contacts">�������� �����: �������; ����� �� 5 ��������; 3-� �������� ������.</div>
<?


	$clients = $APIClient->GetClientsList(array('keyword' => '�������', 'page' => 2, 'count' => 5));

	if (!$APIClient->hasError())
	{
		$clients = process_array($clients);

		echo '<table cellpadding=4 cellspacing=4>';
		foreach ($clients['objects'] as $v)
		{
			echo '<tr><td><p>
				<a href="'.$v['link'].'" target=_blank>'.$v['title'].'</a>
				</p></td><td>'.
				((!empty($v['client_logo'])) ? '<img src="'.$v['client_logo'].'" border=0><br>' : '').
				'</td></tr>';
		}
		echo '</table>';
	}
?>
<h2>������ ��������: GetVacanciesList</h2>
<div class="contacts">�������� �����: php; �����: ������; ����� �� 5 ��������; 2-� �������� ������.</div>
<?


	$vacancies = $APIClient->GetVacanciesList(array('keyword' => 'php', 'town' => 4, 'page' => 1, 'count' => 5));

	if (!$APIClient->hasError())
	{
		$vacancies = process_array($vacancies);

		echo '<table cellpadding=4 cellspacing=4>';
		foreach ($vacancies['objects'] as $v)
		{
			
			echo '<tr><td><p>
				<a href="'.$v['link'].'" target=_blank>'.$v['profession'].'</a>
				</p></td><td>'.
				((!empty($v['client_logo'])) ? '<img src="'.$v['client_logo'].'" border=0><br>' : '').
				'</td></tr>';
		}
		echo '</table>';
	}
?>
<h2 id="oauth">������ �������� � ����������: GetVacanciesList + OAuth</h2>
<div class="contacts">�������� �����: php; �����: �.��������, �����������; ����� �� 10 ��������.</div>
<p><a href="?contacts=1">����������</a></p>
<?
	if (!empty($_REQUEST['contacts']))
	{
		if (empty($_SESSION['oauth_token']))
		{
			$Request = $APIClient->fetchRequestToken();
			$_SESSION['oauth_token'] = $Request->key;
			$_SESSION['oauth_token_secret'] = $Request->secret;
		
			$APIClient->redirectToAuthorizePage($Request, 
				"http://{$_SERVER['HTTP_HOST']}{$_SERVER['SCRIPT_NAME']}?access=1#oauth");
		}
	}
	elseif (!empty($_REQUEST['access']))
	{
		$Access = $APIClient->fetchAccessToken(new OAuthToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']));
		
		$vacancies = 
		$APIClient->setFormat('json')
				->GetVacanciesList(
					array(
						'keyword' => 'php',
						'count' => 10, 
						't' => array(12, 13)
					), 
					$Access
				);
		unset($_SESSION['oauth_token']);
		unset($_SESSION['oauth_token_secret']);
		if (!$APIClient->hasError())
		{
			$vacancies = process_array($vacancies);
			echo '<table cellpadding=4 cellspacing=4>';
			foreach ($vacancies['objects'] as $v)
			{
			
				echo '<tr><td>
					<p><a href="'.$v['link'].'" target=_blank>'.$v['profession'].'</a></p>
					<div class="contacts">'.
						($v['contact'].' &#9679; '.$v['phone'].' &#9679; '.$v['url']).
					'</div>
					</td>
					<td>'.
					((!empty($v['client_logo'])) 
						? '<img src="'.$v['client_logo'].'" border=0><br>' 
						: '').'
					</td></tr>';
			}
			echo '</table>';
		}
		else
		{
			$vacancies = process_array($vacancies);
			// ������� ������ �������� � �������, �� ������ OAuth � ������� ������
			$error = (is_array($vacancies)) ? $vacancies['error']['message'] : $vacancies;
			echo '<p><b>'.$error.'</b></p>';
		}
	}




}
catch (Exception $e)
{
	echo $e->getMessage();
}

?>
<br><br><br><br><br><br><br><br><br><br><br><br>
</div>
</div>