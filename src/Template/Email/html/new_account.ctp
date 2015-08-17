<p>
	<?= $user['User']['name'] ?>,
</p>

<p>
	Your new account on the <a href="<?= $home_url ?>">Community Readiness Initiative website</a> has been created.
</p>

<p>
	You can now <a href="<?= $login_url ?>">log in to the CRI website</a> using the following information:
</p>

<ul>
	<li>
		Email: <?= $user['User']['email'] ?>
	</li>
	<li>
		Password: <?= $user['User']['unhashed_password'] ?>
	</li>
</ul>

<p>
	Once logged in, you can change your password. If you have any questions, please email cri@bsu.edu.
</p>

<p>
	<br />
	<strong>
		Ball State Center for Business and Economic Research
	</strong>
	<br />
	<a href="mailto:cber@bsu.edu">
		cber@bsu.edu
	</a>
	<br />
	<a href="http://www.bsu.edu/cber">
		www.bsu.edu/cber
	</a>
	<br />
	765-285-5926
</p>