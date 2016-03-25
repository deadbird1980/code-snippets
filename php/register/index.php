<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<?php 
  $registered = false;
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $to      = 'gavintanuk@gmail.com';
    $message = 'hello X';
    $subject = "register form({$_POST['frm']['name']})";
    $email = $_POST['frm']['email'];
    foreach($_POST['frm'] as $key=>$val) {
      $message .= "$key: $val\n";
    }

    // In case any of our lines are larger than 70 characters, we should use wordwrap()
    $message = wordwrap($message, 70, "\r\n");
    $headers = 'From: gavintanuk@gmail.com' . "\r\n" .
    'Reply-To: {$email}' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

    mail($to, $subject, $message, $headers);
    $registered = true;
  }
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Register</title>
  <style type="text/css">
  ul { list-style: none; margin: 0; padding: 0; }
  li { margin: .2em 0; }
  
  #register_frm label { 
    float: left; 
    width: 200px; 
    margin-right: 15px; 
    text-align: right; 
  }
  iframe {
    border: 0px;
    width: 100%;
    height: 100%;
  }

  #register {
    width: 400px;
    position: absolute;
    margin: auto;
    left: 0;
    right: 0;
  }
</style>
<script>
  function openForm() {
    var frm = document.getElementById('register');
    frm.style['background-color'] = "white";
    frm = document.getElementById('register_frm');
    if (frm) {
      frm.style.display = "block";
    }
  }
</script>
</head>
<body style="background-image:url(bg.png)">
<div id="register">
<form name="form1" id="mainForm" method="post" enctype="multipart/form-data" action="">
<?php if($registered): ?>
<h2>Register successful!</h2>
<?php endif; ?>
<a href="javascript:void(0);" onclick="openForm();"><h2>Welcome to register with us</h2></a>
<div id="register_frm" style="display:none;">
<ul class="edit_form">
    <li>
        <label for="name">Name</label>
        <input id="name" required name="frm[name]" size="15" type="text" value="">
    </li>
    <li>
        <label for="birthday">Date of Birth</label>
        <input id="birthday" name="frm[birthday]" size="15" type="date" value="">
    </li>
    <li>
        <label for="subject">Interesting Subject</label>
        <input id="subject" required name="frm[subject]" size="20" type="text" value="">
    </li>
    <li>
        <label for="email">Email</label>
        <input id="email" name="frm[email]" size="20" type="email" value="">
    </li>
    <li class="buttons">
        <input name="commit" value="submit" type="submit">
    </li>
</ul>
</div>
</form>
</div>
<!--<iframe height=100% width=100% src="http://www.sheffcol.ac.uk/"></iframe>-->
</body>
</html>
