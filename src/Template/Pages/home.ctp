<?php if ($authUser->role == 'client'): ?>
    <p class="alert alert-info">
        Thank you for participating in the Community Readiness Initiative.
        <strong>
            Visit
            <?= $this->Html->link(
                'Client Home',
                [
                    'prefix' => 'client',
                    'controller' => 'Communities',
                    'action' => 'index'
                ]
            ) ?>
            to get started,
        </strong>
        to check on your community's progress, and to purchase components of the CRI process.
    </p>
<?php endif; ?>

<div id="home">
    <?= $this->Html->link(
        '<img src="/img/sign_up.jpg" alt="The Indiana Office of Community and Rural Affairs invites your community to participate in the Community Readiness Initiative. Enroll now." />',
        [
            'controller' => 'Pages',
            'action' => 'enroll'
        ],
        ['escape' => false]
    ) ?>

    <section>
        <h2>
            What Is the Community Readiness Initiative?
        </h2>
        <p>
            An objective perspective is difficult to gain from within
            your own community. The State of Indiana Office of
            Community of Rural Affairs (OCRA) invites your
            community to participate in the Community Readiness
            Initiative. Through the CRI program, teams will gain insight
            into the strengths and opportunities within their community
            to determine the best course of action to foster better
            community planning and growth for the future.
        </p>

        <p>
            <?= $this->Html->link(
                'View FAQ for communities',
                [
                    'controller' => 'Pages',
                    'action' => 'faq_community'
                ]
            ) ?>
        </p>

        <p>
            Email
            <a href="mailto:cri@bsu.edu">
                cri@bsu.edu
            </a>
            for questions and comments
        </p>
    </section>

    <section>
        <h2>
            Steps Toward Community Readiness
        </h2>
        <ol>
            <li>
                <?= $this->Html->link(
                    'Enroll now',
                    [
                        'controller' => 'Pages',
                        'action' => 'enroll'
                    ]
                ) ?>
                to participate in the Community Readiness Initiative or contact your <a href="http://www.in.gov/ocra/2330.htm">OCRA Community Liaison</a>
            </li>
            <li>
                Complete the leadership alignment assessment (for public officials)
            </li>
            <li>
                Complete the community alignment assessment (for organizations)
            </li>
            <li>
                Discuss preliminary community readiness findings during a town meeting
            </li>
            <li>
                Receive the community readiness report and establish an economic development policy
            </li>
        </ol>
    </section>
</div>