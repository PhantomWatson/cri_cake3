<?php
/**
 * @var \App\View\AppView $this
 * @var string $titleForLayout
 */
?>
<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<p>
    <a href="/files/CRI-Brochure-2016-0119.pdf" class="with_icon">
        <img src="/data_center/img/icons/drive-download.png" />
        <span>
            Printable PDF brochure about CRI
        </span>
    </a>
    <br />
    <a href="/files/CRI-GuideForCommunties-2018-01-16.pdf" class="with_icon">
        <img src="/data_center/img/icons/drive-download.png" />
        <span>
            Printable user guide for communities (January 2018)
        </span>
    </a>
</p>

<div class="faq">
    <section>
        <h2>
            What Is the Community Readiness Initiative?
        </h2>

        <p>
            The Community Readiness Initiative (CRI) is a low-cost, high-impact evaluation process to assist communities in planning for positive, productive growth.
        </p>

        <p>
            The CRI focuses on providing a method of objective, high-quality data-driven analysis to assist communities in building consensus in economic development planning.
        </p>
    </section>

    <section>
        <h2>
            Steps Toward Community Readiness
        </h2>

        <ol>
            <li>
                <?= $this->Html->link(
                    'Sign up now',
                    [
                        'controller' => 'Pages',
                        'action' => 'enroll'
                    ]
                ) ?>
                to participate in the Community Readiness Initiative
            </li>
            <li>
                Complete leadership alignment assessment (for public officials)
            </li>
            <li>
                Complete the community alignment assessment (for organizations)
            </li>
            <li>
                Discuss preliminary community readiness findings during a town meeting
            </li>
            <li>
                Receive the community readiness final report and establish economic development policy
            </li>
        </ol>

        <img src="/img/Flowchart-20160119.png" class="process" />
    </section>

    <section>
        <h2>
            How Long Does the CRI Take to Complete?
        </h2>

        <p>
            Because most of the steps require feedback from many individuals and groups, completion of each step will vary widely, with some taking a few weeks and others taking a few months.
        </p>
    </section>

    <section>
        <h2>
            What Is My Community Getting Ready for?
        </h2>

        <p>
            The Community Readiness Initiative will help communities understand where they are so that they can develop grounded policies based on where they can confidently go to build and sustain economic self-sufficiency guided by quantitative research and informed analysis.
        </p>

        <p>
            The CRI is an opportunity to align the entire community using quantitative economic data about itself and to build consensus about the highest potential strategies so that together, the entire community can move forward in economic development planning.
        </p>

        <p>
            Communities are evaluated using PWR<sup>3</sup>, an analytical tool designed to help economic development community planning by addressing the five economic activities of <strong>P</strong>roduction, <strong>W</strong>holesale, <strong>R</strong>etail, <strong>R</strong>esidential, and <strong>R</strong>ecreation.
            PWR<sup>3</sup> lets policy makers better understand the roles that community assets and limitation play in forging a place within the regional economy. It would also enable communities to determine in which of the PWR<sup>3</sup> elements they possess strength and how to leverage those strengths to increase regional wealth.
            <a href="/files/PWR3 Assessment (Heupel, Hicks and Devaraj).pdf">Learn more about PWR<sup>3</sup> (PDF)</a>
        </p>

        <p>
            Completion of all four steps in the CRI will prepare communities to work with OCRA and other agencies on future projects.
        </p>
    </section>

    <section>
        <h2>
            How Do I Sign Up My Community?
        </h2>

        <p>
            Enroll online now or contact your OCRA Community Liaison.
        </p>

        <p>
            The CRI team will then contact you to begin the process. For questions in the interim, contact your OCRA Community Liaison or email the Ball State CRI team at <a href="mailto:cri@bsu.edu">cri@bsu.edu</a>.
        </p>

        <p>
            Each community should assemble a local team (such as town council members, economic development persons, or special committees) to sponsor this project. One person should be identified to serve as the primary contact throughout the CRI process.
        </p>
    </section>
</div>
