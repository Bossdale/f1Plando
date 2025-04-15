<!DOCTYPE html> 
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Activity 4</title>
	<style type="text/css">
		.form{
			margin: 80px auto;
			padding: 35px;
			width: 360px;
			border-style: solid;
			border-color: #a91901;
			background-color: rgba(255, 255, 255, 0.8);
			box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
			padding-top: 5px;
			border-radius: 30px;
		}
		.form h2{
			font-family: Cooper Black;
			font-size: 30px;
			color: #a91901;
		}
		body {
			margin: 0;
			padding: 0;
			min-height: 400vh; /* Increased height to create more space for scrolling */
			overflow-y: scroll;
			background-image: url('bg.jpg'); /* Add the background image */
			background-size: cover; /* Ensure the image covers the entire page */
			background-position: center; /* Center the image */
			background-repeat: no-repeat; /* Prevent the image from repeating */
			background-attachment: fixed;
		}
		#head{
			text-align: center;
			font-family: Cooper Black;
			font-size: 35px;
		}
		.project-title {
    		position: fixed;
    		top: 0;
    		left: 0;
    		width: 100%;
    		display: flex;
    		justify-content: space-between;
    		align-items: center;
    		font-family: Arial, sans-serif;
    		font-size: 30px;
    		color: white;
    		background-color: #a91901; /* Solid color instead of rgba */
    		padding: 15px 5px; /* Increased padding on the sides */
    		border-radius: 10px;
    		z-index: 5; /* Ensure the title is always above other content */
		}
		.project-title h1 {
			margin: 0;
		}
		.nav-links {
			display: flex;
		}
		.nav-links {
    		display: flex;
    		margin-left: 30px; /* Added margin to shift the links slightly to the left */
		}
		.nav-links a {
    		color: white;
    		text-decoration: none;
    		margin-left: 10px; /* Reduced the space between links */
    		font-size: 16px;
		}

		.label{
			font-family: verdana;
			display: inline-block;
			text-align: left;
			padding-left: 5px;
			padding-right: 10px;
			width: 95px;
		}
		.input{
			text-align: center;
			width: 190px;	
			margin-right: 10px;
			margin-left: 30px;
		}
		.button{
			margin-top: 20px;
			margin-left: 130px;
			padding: 5px;
			width: 100px;
		}
		.footer {
    		text-align: center;
    		position: fixed;
    		bottom: 0;
    		width: 100%;
    		font-family: Verdana;
    		font-size: 15px;
    		color: white;
    		background-color: #a91901; /* Solid color instead of rgba */
    		padding: 10px 0;
    		z-index: 3;
		}
		/* About Us Section */
		.about-us {
			margin: 400px auto; /* Increased margin-top to push it further down */
			width: 80%;
			max-width: 800px;
			background-color: rgba(255, 255, 255, 0.8);
			padding: 30px;
			border-radius: 15px;
			box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
			text-align: center;
		}
		.about-us h2 {
			font-family: Cooper Black;
			font-size: 30px;
			color: #a91901;
		}
		.about-us p {
			font-family: Verdana;
			font-size: 16px;
			line-height: 1.6;
			color: #333;
		}
		/* Contact Us Section */
		.contact-us {
			margin: 600px auto; /* Pushed the section further down */
			width: 80%;
			max-width: 800px;
			background-color: rgba(255, 255, 255, 0.8);
			padding: 30px;
			border-radius: 15px;
			box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
			text-align: center;
		}
		.contact-us h2 {
			font-family: Cooper Black;
			font-size: 30px;
			color: #a91901;
		}
		.contact-us p {
			font-family: Verdana;
			font-size: 16px;
			line-height: 1.6;
			color: #333;
		}
	</style>
</head>
<body>
<div>
	<!-- Project Title with Navigation (fixed at the top) -->
	<div class="project-title">
		<h1>Tindahan Titans</h1>
		<div class="nav-links">
			<a href="#login">Login</a>
			<a href="#about-us">About Us</a>
			<a href="#contact-us">Contact Us</a>
		</div>
	</div>

	<!-- Log-In Form with the id "login" -->
	<form class="form" id="login">
		<h2 id="head">Log-In</h2><br>
		<label for="username" class="label"><b>Username: </b></label>
		<input type="text" class="input" id="username" required>
		<br><br>
		<label for="password" class="label"><b>Password: </b></label>
		<input type="password" id="password" class="input">
		<button class="button" type="Submit"><b>Login</b></button>
	</form>

	<!-- About Us Section -->
	<div class="about-us" id="about-us">
		<h2>About Us</h2>
		<p>
			Welcome to Tindahan Titans — the ultimate Sari-sari store management system designed to empower local business owners and bring ease to managing your store's day-to-day operations.

At Tindahan Titans, we understand the unique challenges that come with running a Sari-sari store, from tracking inventory to managing sales and customer orders. Our platform is designed to simplify these tasks and optimize the way you run your store.

Our mission is to provide a powerful yet user-friendly system that helps you manage stock, track sales, monitor profits, and keep your business running smoothly. Whether you're a small neighborhood store or a growing chain of Sari-sari shops, Tindahan Titans offers customizable tools and features tailored to fit your needs.

		</p>
	</div>

	<!-- Contact Us Section -->
	<div class="contact-us" id="contact-us">
		<h2>Contact Us</h2>
		<p>
			If you have any questions or concerns, feel free to reach out to us! Our team is here to help.
			You can contact us at the following:
		</p>
		<p><strong>Email:</strong> support@tindahantitans.com</p>
		<p><strong>Phone:</strong> +631231232</p>
	</div>
</div>

<div class="footer">
	<p>Kyle Plando | BSCS - 2</p>
</div>

</body>
</html>