<?php
    use Cake\Routing\Router;
?>
<div class="page-header">
    <h1>
        <?php echo $titleForLayout; ?>
    </h1>
</div>

<p>
    Select a section below for more information about using the CRI website as an administrator.
</p>

<section class="admin-guide">
    <h2>
        The CRI Process
    </h2>
    <div>
        <p>
            The 'Progress' page for each community gives details about what criteria it has passed and should be
            consulted before advancing communities through the five steps of the CRI program.
        </p>
        <ol>
            <li>
                <strong>Create a community</strong>
            </li>
            <li>
                <strong>Add clients</strong> to that community
            </li>
            <li>
                Create, link, and activate the community's <strong>"Community Officials" questionnaire</strong>
            </li>
            <li>
                Advance the community to <strong>Step Two</strong> and inform the client
            </li>
            <li>
                <strong>Deactivate the questionnaire</strong> once enough responses have been collected
            </li>
            <li>
                Add the dates of <strong>Presentations A and B</strong>
            </li>
            <li>
                Create, link, and activate the community's <strong>"Community Organizations" questionnaire</strong>
            </li>
            <li>
                Advance the community to <strong>Step Three</strong> once it has passed all of the Step Two criteria,
                and inform the client
            </li>
            <li>
                <strong>Deactivate the questionnaire</strong> once enough responses have been collected
            </li>
            <li>
                Add the date of <strong>Presentation C</strong>
            </li>
            <li>
                Advance the community to <strong>Step Four</strong> once it has passed all of the Step Three criteria
            </li>
            <li>
                Advance the community to <strong>Step Five</strong> once its participation in CRI has
                concluded
            </li>
        </ol>
    </div>
</section>

<section class="admin-guide">
    <h2>Adding a New Community</h2>
    <ol>
        <li>
            <p>
                <strong>Add Community</strong>
            </p>
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
                    Stage / PWR<sup>3</sup> &trade; Score, or Public
                    from their default values.
                </li>
            </ol>
        </li>
        <li>
            <p>
                <strong>Add Client(s)</strong>
            </p>
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
            <p>
                You can repeat this step if multiple clients need accounts created for them.
            </p>
            </ol>
        </li>
    </ol>
</section>

<section class="admin-guide">
    <h2>Setting Up a Questionnaire</h2>
    <ol>
        <li>
            <p>
                <strong>Create in SurveyMonkey</strong>
            </p>
            <ol>
                <li>
                    Log in to <a href="http://surveymonkey.com/">SurveyMonkey</a>
                </li>
                <li>
                    Click 'Create Survey'
                </li>
                <li>
                    Select 'Edit a Copy of an Existing Survey'
                </li>
                <li>
                    Select the appropriate template survey (e.g. "TEMPLATE: Leader Alignment Questionnaire - Town (County)")
                </li>
                <li>
                    Change the new survey's title to either "Leader Alignment Questionnaire - "
                    or "Community Organizations Questionnaire - " and then append the name of the community
                </li>
                <li>
                    Click "Let's Go!"
                </li>
                <li>
                    Click on the 'Collect Responses' tab
                </li>
                <li>
                    Select 'Web Link Collector'
                </li>
                <li>
                    Click 'Next'
                </li>
            </ol>
            <p>
                That's it. This survey's web collector is now set up. Further configuration options can be ignored.
            </p>
        </li>
        <li>
            <p>
                <strong>Link and Activate</strong>
            </p>
            <p>
                After creating the questionnaire in SurveyMonkey, use these steps to allow the CRI site to connect to it.
            </p>
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
                    page
                </li>
                <li>
                    Find the row corresponding to the new community
                </li>
                <li>
                    For that community, the column corresponding to this survey type (either <em>officials</em> or
                    <em>organizations</em>) will display 'Not set up' if the questionnaire hasn't been linked yet.
                    Click on 'Not set up' to open the menu.
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
                    Select the 'Also activate questionnaire' option *
                </li>
                <li>
                    Click 'Link Questionnaire'
                </li>
            </ol>
            <p>
                * If you don't immediately activate the questionnaire, you can activate it later by going to its
                'Activate' page.
            </p>
        </li>
    </ol>
</section>

<section class="admin-guide">
    <h2>Deactivating a Questionnaire</h2>
    <ol>
        <li>
            Click on <?= $this->Html->link(
                'Manage communities',
                [
                    'prefix' => 'admin',
                    'controller' => 'Communities',
                    'action' => 'index'
                ]
            ) ?> in the sidebar
        </li>
        <li>
            Find the row and column associated with the community and type of questionnaire you'd like to deactivate
        </li>
        <li>
            Click the menu for that questionnaire and select 'Deactivate'
        </li>
        <li>
            On the survey activation/deactivation page that opens, click 'Deactivate'
        </li>
    </ol>
</section>

<section class="admin-guide">
    <h2>Recording Presentation Dates</h2>
    <ol>
        <li>
            Click on <?= $this->Html->link(
                'Manage communities',
                [
                    'prefix' => 'admin',
                    'controller' => 'Communities',
                    'action' => 'index'
                ]
            ) ?> in the sidebar
        </li>
        <li>
            Find the row associated with the appropriate community
        </li>
        <li>
            Click on 'Actions'
        </li>
        <li>
            Click on 'Presentations'
        </li>
        <li>
            For the appropriate presentation (A, B, or C), select 'Scheduled' and input the date of the presentation
        </li>
        <li>
            Click 'Update'
        </li>
    </ol>
</section>

<section class="admin-guide">
    <h2>Advancing Communities Through the CRI Steps</h2>
    <ol>
        <li>
            Click on <?= $this->Html->link(
                'Manage communities',
                [
                    'prefix' => 'admin',
                    'controller' => 'Communities',
                    'action' => 'index'
                ]
            ) ?> in the sidebar
        </li>
        <li>
            Find the row corresponding to the community you want to advance
        </li>
        <li>
            Click on the 'Actions' menu
        </li>
        <li>
            Click on 'Progress'
        </li>
        <li>
            Confirm that this community has passed all of the necessary criteria to advance
        </li>
        <li>
            Select the step that you would like to advance this community to
        </li>
        <li>
            Click 'Update'
        </li>
    </ol>
</section>

<section class="admin-guide">
    <h2>Managing Payment Records</h2>
    <div>
        <p>
            On the
            <?= $this->Html->link(
                'Payment Records',
                [
                    'prefix' => 'admin',
                    'controller' => 'Purchases',
                    'action' => 'index'
                ]
            ) ?>
            page, records of all purchases and refunds can be reviewed and updated.
        </p>
        <ul>
            <li>
                <strong>To manually add a purchase record</strong>, which will be necessary if a client makes a payment
                without going through the CRI website / CASHNet payment system (e.g. by check) or if a purchase is paid
                for by someone other than the client (e.g. OCRA), click on
                <?= $this->Html->link(
                    'Add Payment Record',
                    [
                        'prefix' => 'admin',
                        'controller' => 'Purchases',
                        'action' => 'add'
                    ]
                ) ?>.
            </li>
            <li>
                <strong>To record a refund</strong>, click on 'refund' in the row corresponding to the appropriate
                payment. This does not issue a refund, but just records that one has been issued. When a purchase is
                refunded, whatever CRI website access that the purchase granted the community will be immediately
                revoked.
            </li>
            <li>
                <strong>To review details</strong> such as which administrator marked a payment as having been refunded, which client
                made the purchase, or what notes were added by an administrator when a purchase record was manually added, click 'Details'.
            </li>
        </ul>
    </div>
</section>

<section class="admin-guide">
    <h2>Admin Page: Manage Communities</h2>
    <div>
        <p>
            On the
            <?= $this->Html->link(
                'Manage Communities',
                [
                    'prefix' => 'admin',
                    'controller' => 'Communities',
                    'action' => 'index'
                ]
            ) ?>
            page, communities are listed along with their current stage and questionnaire statuses.
        </p>

        <p>
            <strong>Questionnaire menus</strong>
        </p>
        <ul>
            <li>
                <strong>Overview</strong>
                <br />
                Shows the current status of this questionnaire, and allows administrators to
                import and view responses and manage unapproved respondents
            </li>
            <li>
                <strong>Link</strong>
                <br />
                Allows an administrator to connect a new questionnaire to its corresponding community
            </li>
            <li>
                <strong>Activate / Deactivate</strong>
                <br />
                Turns invitation-sending and response-collecting for this questionnaire on or off
            </li>
            <li>
                <strong>Invitations</strong>
            </li>
            <li>
                <strong>Reminders</strong>
            </li>
            <li>
                <strong>Alignment</strong>
                <br />
                Shows this community's calculated PWR<sup>3</sup> and internal alignment for this questionnaire
            </li>
        </ul>

        <strong>
            Actions menu
        </strong>
        <ul>
            <li>
                <strong>Progress</strong>
                <br />Shows which of the five steps of CRI this community is currently on and what criteria for advancement have been passed in order to help administrators determine if it is ready for advancement.
                <br />A community's stage/score can be updated through this page.
            </li>
            <li>
                <strong>Presentations</strong>
                <br />Used for recording the dates of CRI presentations
            </li>
            <li>
                <strong>Clients</strong>
                <br />Lists the clients associated with this community and provides links for adding a new client account
                or associating an existing client account with this community
            </li>
            <li>
                <strong>Client Home</strong>
                <br />Shows the homepage that this community's client sees
            </li>
            <li>
                <strong>Performance Charts</strong>
                <br />Shows the performance charts for the geographic area that this community
            </li>
            <li>
                <strong>Notes</strong>
                <br />This is where arbitrary notes about the community can be recorded and reviewed
            </li>
            <li>
                <strong>Edit Community</strong>
                <br />This is where the information entered in the 'Add Community' form can be updated
            </li>
            <li>
                <strong>Delete Community</strong>
                <br />This is how you would delete a fake community that you set up for testing. This is permanent, so please use this with caution.
            </li>
        </ul>
    </div>
</section>

<section class="admin-guide">
    <h2>Other Admin Functions</h2>
    <ul>
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
        <li>
            <strong>Send test email</strong>
            <br />
            To send a test email to confirm that CRI's email-sending process is working correctly,
            visit the URL
            <?= Router::url([
                'prefix' => false,
                'controller' => 'Pages',
                'action' => 'sendTestEmail'
            ], true) ?>/youremail@example.com.
        </li>
    </ul>
</section>

<?php $this->element('script', ['script' => 'admin']); ?>
<?php $this->append('buffered'); ?>
    adminGuide.init();
<?php $this->end(); ?>
