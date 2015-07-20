<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<h2>Admin functions</h2>
<ul>
	<li>
		<strong><a href="http://cri.cberdata.org/admin/users">Manage users</a></strong>
		<br />Here, additional admins, consultants, and clients can be added.
	</li>
	<li>
		<strong><a href="http://cri.cberdata.org/admin/communities">Manage communities</a></strong>
		<br />Here, you can add new communities as clients sign up. (more about adding communities further down)
		<br />This provides a list of communities and shows which have clients associated with them and which the general public can access charts and tables for.
		<br />For client communities, the following links are found under "Actions":
		<ul>
			<li>
				<strong>Progress</strong>
				<br />Shows what step the community is currently on (if 'score' > 0) and what criteria for advancement have been passed.
				<br />Intended to be a guide to help administrators quickly determine whether a community is ready for advancement or not.
				<br />A community's score can be updated through this page.
			</li>
			<li>
				<strong>Clients</strong>
				<br />Lists the clients associated with this community and their email addresses
			</li>
			<li>
				<strong>Performance Charts</strong>
			</li>
			<li>
				<strong>Edit</strong>
				<br />This page is where a community's settings, clients, consultants, surveys, and score are all managed.
			</li>
			<li>
				<strong>Delete</strong>
				<br />This is how you would delete a fake community that you set up for testing. This is permanent, so please use this with caution.
			</li>
		</ul>
		Clicking on a community's name reveals links to <strong>survey overview pages</strong> for any surveys that have been created. On those pages, you can
		<ul>
			<li>
				View and send <strong>invitations</strong>
			</li>
			<li>
				Import and view <strong>responses</strong>
			</li>
			<li>
				Manage <strong>unapproved respondents</strong>
			</li>
			<li>
				View the community's calculated <strong>alignment</strong>, set its alignment, and set its pass/fail status.
			</li>
		</ul>
	</li>
	<li>
		<strong>Adding/editing communities</strong>
		<br />When new user accounts are created, an email automatically goes out to the new user telling them what their password is and where they can log in.
		<br />Once a survey is created in SurveyMonkey, it must be looked up and selected on the community add/edit page. This "links" the community's CRI account and its SurveyMonkey surveys.
		<br />Only CRI surveys can be selected, because the CRI site looks through the selected survey for the specific question used in alignment calculation.
	</li>
	<li>
		<strong>Making test purchases</strong>
		<br />On any page that displays a link to purchase a product, add ?debug at the end of the URL and load the resulting address. This will switch the page over to debug mode and the links will send you to a fake purchase page. If you enter the credit card number 5454545454545454 and any other valid information, the purchase will be treated as valid without actually charging any credit card and the CRI site will immediately be informed of the purchase.
	</li>
	<li>
		<strong>"View As Client"</strong>
		<br />This section of the sidebar lets you select a client and view their Client Home page as if you were logged in as them.
	</li>
</ul>

<h2><a href="http://cri.cberdata.org/client">Client Home</a></h2>
<ul>
	<li>
		This is the primary page that clients should be visiting.
		<br />An elaborated version of the 'view community progress page', this shows the community's current stage, lists what advancement criteria have been passed, and displays action buttons for making purchases, sending invitations, importing responses, etc.
	</li>
</ul>

<h2>Suggestions</h2>
<ul>
	<li>Fiddle with Test Community.</li>
	<li>Explore the whole site, including the admin and client sections.</li>
	<li>Let me know if anything is confusing or behaves in an unexpected way.</li>
	<li>Let me know if any test should be edited.</li>
</ul>

<h2>Stuff that's not done yet</h2>
<ul>
	<li>Anything for consultants to do after they've logged in</li>
	<li>Automating the sending of "hey, your survey's ready" emails to clients</li>
	<li>Checking for new clients signing up in SurveyMonkey and automatically alerting CBER admins</li>
	<li>Facilitating/automating sending reminder emails to invited survey participants who haven't submitted responses</li>
</ul>