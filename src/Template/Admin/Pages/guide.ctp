<div class="page-header">
    <h1>
        <?php echo $titleForLayout; ?>
    </h1>
</div>

<h2>Adding a New Community</h2>
<ol>
    <li>
        <strong>Create a Community Record</strong>
        <ol>
            <li>
                Click on
                <?= $this->Html->link(
                    'Manage Communities',
                    [
                        'prefix' => 'admin',
                        'controller' => 'Communities',
                        'action' => 'index'
                    ]
                ) ?> in the sidebar
            </li>
            <li>
                Click on <?= $this->Html->link(
                    'Add Community',
                    [
                        'prefix' => 'admin',
                        'controller' => 'Communities',
                        'action' => 'add'
                    ]
                ) ?> in the page that opens
            </li>
            <li>
                Fill out the form with that community's basic information and submit it.
                You will probably <em>not</em> need to change the fields
                Stage / PWR<sup>3</sup> &trade; Score, Meeting Date Set, or Public
                from their default values.
            </li>
        </ol>
    </li>
    <li>
        <strong>Create the Community Officials Questionnaire</strong>
        <br />
        Log in to <a href="http://surveymonkey.com/">SurveyMonkey</a> and create a new
        community-officials questionnaire:
        <ol>
            <li>
                Click 'Create Survey'
            </li>
            <li>
                Select 'Edit a Copy of an Existing Survey'
            </li>
            <li>
                Select the template survey, called "TEMPLATE: Leader Alignment Questionnaire - Town (County)"
            </li>
            <li>
                Change the new survey's title to "Leader Alignment Questionnaire - " and then appending it with the name of the community
            </li>
            <li>
                Click "Let's Go!"
            </li>
        </ol>
        Next, create a web collector:
        <ol>
            <li>
                Click on the 'Collect Responses' tab
            </li>
            <li>
                Select 'Web Link Collector'
            </li>
            <li>
                Click 'Next'
            </li>
            <li>
                That's it. This survey's web collector is now set up. Further configuration options can be ignored.
            </li>
        </ol>
    </li>
    <li>
        <strong>Link the Questionnaire</strong>
        <br />
        The CRI site needs to know how to connect to the correct SurveyMonkey questionnaire for this community.
        <ol>
            <li>
                In the
                <?= $this->Html->link(
                    'Manage Communities',
                    [
                        'prefix' => 'admin',
                        'controller' => 'Communities',
                        'action' => 'index'
                    ]
                ) ?>
                page, click on 'Not set up' under 'Officials Questionnaire' in the row corresponding
                to the new community
            </li>
            <li>
                Select 'Link to SurveyMonkey questionnaire'
            </li>
            <li>
                On the page that opens, click 'Select Questionnaire'
            </li>
            <li>
                Select the correct questionnaire from the list that appears
            </li>
            <li>
                If there are no errors, the status will display "Ready to be linked"
            </li>
            <li>
                Click 'Link Questionnaire'
            </li>
        </ol>
    </li>
    <li>
        <strong>Create a Client Account</strong>
        <ol>
            <li>
                In the
                <?= $this->Html->link(
                    'Manage Communities',
                    [
                        'prefix' => 'admin',
                        'controller' => 'Communities',
                        'action' => 'index'
                    ]
                ) ?>
                page, click on 'Actions' and then 'Clients'
            </li>
            <li>
                In the next page, click 'Add a New Client'
            </li>
            <li>
                Fill out the following form using a random password and submit it. The client will be automatically sent an
                email with their login information.
            </li>
        </ol>
    </li>
    <li>
        You can also use the above methods to create additional clients accounts and create / link the community-organizations questionnaire.
    </li>
</ol>

<h2>Admin Functions</h2>
<ul>
    <li>
        <strong>
            <?= $this->Html->link(
                'Manage users',
                [
                    'prefix' => 'admin',
                    'controller' => 'Users',
                    'action' => 'index'
                ]
            ) ?>
        </strong>
        <br />Here, admins and client accounts can be added, edited, or deleted.
    </li>
    <li>
        <strong>
            <?= $this->Html->link(
                'Manage communities',
                [
                    'prefix' => 'admin',
                    'controller' => 'Communities',
                    'action' => 'index'
                ]
            ) ?>
        </strong>
        <br />Here, communities are listed along with their current stage, fast track status, and questionnaire statuses.
        <br />The following links are found under 'Actions':
        <ul>
            <li>
                <strong>Progress</strong>
                <br />Shows what step the community is currently on (if 'score' > 0) and what criteria for advancement have been passed.
                <br />Intended to be a guide to help administrators quickly determine whether a community is ready for advancement or not.
                <br />A community's stage/score can be updated through this page.
            </li>
            <li>
                <strong>Clients</strong>
                <br />Lists the clients associated with this community and provides links for adding a new client account
                or associating an existing client account with this community
            </li>
            <li>
                <strong>Client Home</strong>
                <br />Shows the homepage that this community's client sees
            <li>
                <strong>Officials Questionnaire / Organizations Questionnaire</strong>
                <br />On these questionnaire overview pages, you can
                <ul>
                    <li>
                        Create or update the <strong>link</strong> between the community record and each SurveyMonkey questionnaire
                    </li>
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
                        View the community's calculated <strong>alignment</strong>, set its alignment, and set its pass/fail status
                    </li>
                </ul>
            </li>
            <li>
                <strong>Performance Charts</strong>
                <br />Shows the performance charts for the geographic area that this community
            </li>
            <li>
                <strong>Edit</strong>
                <br />This is where the information entered in the 'Add Community' form can be updated
            </li>
            <li>
                <strong>Delete</strong>
                <br />This is how you would delete a fake community that you set up for testing. This is permanent, so please use this with caution.
            </li>
        </ul>
    </li>
    <li>
        <strong>
            <?= $this->Html->link(
                'Payment Records',
                [
                    'prefix' => 'admin',
                    'controller' => 'Purchases',
                    'action' => 'index'
                ]
            ) ?>
        </strong>
        <br />Here, records of all purchases and refunds can be reviewed.
        <ul>
            <li>
                <strong>To record a refund</strong>, click on 'refund' in the corresponding row. This does not issue a refund, but
                just records that one has been issued. When a purchase is refunded, whatever website access that the purchase
                granted the community will be immediately revoked.
            </li>
            <li>
                <strong>To manually add a purchase record</strong>, which will be necessary if a client makes a payment without going
                through the CRI website / CASHNet payment system (e.g. by check), click on 'Add Purchase Record'.
            </li>
            <li>
                <strong>To review details</strong> such as which administrator marked a payment as having been refunded, which client
                made the purchase, or what notes were added by an administrator when a purchase record was manually added, click 'Details'.
            </li>
        </ul>
    </li>
    <li>
        <strong>
            <?= $this->Html->link(
                'Fix missing PWR<sup>3</sup> alignment',
                [
                    'prefix' => 'admin',
                    'controller' => 'Responses',
                    'action' => 'calculateMissingAlignments'
                ],
                ['escape' => false]
            ) ?>
        </strong>
        <br />
        Are any responses missing their local_area_pwrrr_alignment or parent_area_pwrrr_alignment values?
        Visit the page linked above and missing alignment values will be calculated and saved.
    </li>
</ul>
