<div class="page-header">
    <h1>
        <?php echo $titleForLayout; ?>
    </h1>
</div>

<h2>Adding a New Community</h2>
<ol>
    <li>
        <strong>Create a Community Record</strong>
        <br />
        Click on
        <?= $this->Html->link(
            'Manage Communities',
            [
                'prefix' => 'admin',
                'controller' => 'Communities',
                'action' => 'index'
            ]
        ) ?>
        , then
        <?= $this->Html->link(
            'Add Community',
            [
                'prefix' => 'admin',
                'controller' => 'Communities',
                'action' => 'add'
            ]
        ) ?>.
        Fill out the form with that community's basic information and submit it.
        You will probably <em>not</em> need to change the fields
        Stage / PWR<sup>3</sup> &trade; Score, Meeting Date Set, or Public
        from their default values.
    </li>
    <li>
        <strong>Create the Community Officials Survey</strong>
        <br />
        Log in to <a href="http://surveymonkey.com/">SurveyMonkey</a> and create a new
        community officials survey for this community by
        <ol>
            <li>
                Clicking 'Create Survey'
            </li>
            <li>
                Selecting 'Edit a Copy of an Existing Survey'
            </li>
            <li>
                Selecting the template survey, called "Leader Alignment Data Request (TEMPLATE)"
            </li>
            <li>
                Changing the new survey's title to "Leader Alignment Data Request - " and then appending it with the name of the community
            </li>
            <li>
                Clicking "Let's Go!"
            </li>
        </ol>
        And remember to create a web collector by
        <ol>
            <li>
                Clicking on the 'Collect Responses' tab
            </li>
            <li>
                Selecting 'Web Link Collector'
            </li>
            <li>
                Clicking 'Next'
            </li>
            <li>
                That's it. This survey's web collector is now set up. Further configuration options can be ignored.
            </li>
        </ol>
    </li>
    <li>
        <strong>Link the Survey</strong>
        <br />
        In the Manage Communities page, click on 'Actions' and then 'Officials Survey' in the row corresponding
        to the new community. If this CRI community has not been linked to this SurveyMonkey survey yet, you'll
        be sent to the 'survey linking' page.
        <br />
        Click 'Select Survey', then select the correct survey from the list that appears. If there are no errors,
        the status will display "Ready to be linked". Click 'Link Survey'.
    </li>
    <li>
        <strong>Create a Client Account</strong>
        <br />
        In the Manage Communities page, click on 'Actions' and then 'Clients'. In the next page, click 'Add a New Client'.
        Fill out the following form using a random password and submit it. The client will be automatically sent an
        email with their login information.
    </li>
    <li>
        You can also use the above methods to create additional clients accounts and create / link the community organizations survey.
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
        <br />Here, communities are listed along with their current stage, fast track status, and survey statuses.
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
                <strong>Officials Survey / Organizations Survey</strong>
                <br />On these survey overview pages, you can
                <ul>
                    <li>
                        Create or update the <strong>link</strong> between the community record and each SurveyMonkey survey
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

<h2>Features Not Yet Implemented</h2>
<ul>
    <li>Consultant accounts</li>
    <li>Automatically alerting administrators of new enrollment applications</li>
    <li>Facilitating/automating reminder emails to invited survey participants who haven't submitted responses</li>
</ul>