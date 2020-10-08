<?php
	session_start();
	require('../settings.php');
	unset($_SESSION[$setting['personal']['token']]);
?><html>
	<head>
		<title>Login</title>
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="viewport" content="user-scalable=no, width=device-width" />
		<meta name="viewport" content="minimum-scale=1.0,width=device-width,maximum-scale=1,user-scalable=no" />
		<style>
			body {background-color:grey;color:white;font-family:arial}
			form {height:100%;position:relative}
			table {margin: 0;position:absolute;top:50%;left:50%;-ms-transform:translate(-50%, -50%);transform:translate(-50%, -50%);}
			h1 {margin:0;}
			input,select{padding:4px;background-color:#353944;border:none;color:white;font-size:20px;-webkit-appearance: none;-moz-appearance: none;border-radius:none;border:none;outline:none}
			input[type=submit]{background-color:#2069A1;padding:10px 20px;cursor:pointer;}
			div {margin:30px 0 0px 0;color:grey}
			select {text-indent: 1px;text-overflow: '';}
			select:active{border:none}
			@media (max-width: 768px) {
				#logo{display:none;}
				table{width:100%;margin:0}
			}
		</style>
	</head>
	<body>
		<form action='../' method='post'>
			<table width='800px' cellpadding='20' cellspacing='0' autocomplete="off">
				<tr>
					<td id='logo' bgcolor='#2069A1' width='50%' valign='center' align='center' style='font-size:130px'>
						&#9813;
					</td>
					<td bgcolor='#353944' width='50%' valign='top'>
						<h1>Login</h1>
						<div>USERNAME</div>
						<input name='username' type='text' placeholder='Enter your username' name="unx" autofill="off" autocomplete="fuck" autocorrect="off" spellcheck="false" autocapitalize="off" />
						<div>PASSWORD</div>
						<input name='password' type='password' placeholder='Enter your password' autocomplete='off' autocorrect='off' spellcheck='false' autocapitalize='off' />
						<div>ACCOUNT TYPE</div>
						<select name='type'>
							<option selected>Select your account &nbsp;</option>
							<option value='member'>Member</option>
							<option value='operator'>Operator</option>
							<option value='admin'>Admin</option>
						</select>
						<div></div>
						<input type='submit' value='Sign In' />
					</td>
				</tr>
			</table>
		</form>