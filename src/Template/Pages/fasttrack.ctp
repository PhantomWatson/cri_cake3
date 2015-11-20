<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<section>
    <h2>
        What Is the CRI Fast Track?
    </h2>

    <p>
        Communities that have recently completed a detailed strategic economic or community development planning process (within the last five years) may apply for the CRI Fast Track, a streamlined version of the CRI program.
    </p>

    <p>
        The community must submit
        <strong>1)</strong> a comprehensive strategic plan that demonstrates an understanding of the community's current economic situation and physical infrastructure and outlines the implementation of effective strategic goals for positive growth;
        <strong>2)</strong> a description of how these goals are being implemented;
        and <strong>3)</strong> a description of the community's open planning process.
    </p>

    <p>
        To apply for the CRI Fast Track,
        <?= $this->Html->link(
            'sign up to participate',
            [
                'controller' => 'Pages',
                'action' => 'enroll'
            ]
        ) ?>
        and select "Fast Track" during the enrollment process.  It will be a selectable option on the last page of the application form.
    </p>
</section>