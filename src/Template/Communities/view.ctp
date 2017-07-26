<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<section class="community_data_section">
    <h2>
        PWR<sup>3</sup> &trade; Performance
    </h2>
    <p>
        The PWR<sup>3</sup> &trade; (Power Cubed) name is an acronym representing the five economic activities
        and elements that are broadly applicable to any regional economy and business sector:
        <strong>P</strong>roduction,
        <strong>W</strong>holesale,
        <strong>R</strong>etail,
        <strong>R</strong>esidential, and
        <strong>R</strong>ecreation.
        This information will help you identify the unique combination of strengths in your region and
        determine how to leverage those strengths for effective community growth.
    </p>

    <ul class="nav nav-tabs" role="tablist">
        <?php foreach($barChart as $areaScope => $chart): ?>
            <?php if (! $chart) continue; ?>
            <li role="presentation">
                <a href="#tabpanel-<?= $areaScope ?>-barchart" aria-controls="tabpanel-<?= $areaScope ?>-barchart" role="tab" data-toggle="tab">
                    <?= $areas[$areaScope] ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="tab-content">
        <?php foreach($barChart as $areaScope => $chart): ?>
            <?php if (! $chart) continue; ?>
            <div role="tabpanel" class="tab-pane active" id="tabpanel-<?= $areaScope ?>-barchart">
                <?php $chart->div($areaScope.'_bar_chart_container'); ?>
                <div id="<?= $areaScope ?>_bar_chart_container"></div>
                <?php $this->GoogleCharts->createJsChart($chart); ?>
                <p class="footnote">
                    Data Source: Author calculations and Bureau of Economic Analysis
                </p>

                <button id="<?= $areaScope ?>_pwr_table_toggler" class="btn btn-primary">
                    Show table
                </button>

                <?= $this->element('Communities'.DS.'pwrrr_table', compact('areaScope')) ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="community_data_section">
    <h2>
        Exportable and Non-Exportable Sector Employment, 1969-2011
    </h2>
    <p>
        This figure shows the balance between employment in exportable and non-exportable sectors.
        Note the changes around each recessionary period.
    </p>

    <ul class="nav nav-tabs" role="tablist">
        <?php foreach($lineChart as $areaScope => $chart): ?>
            <?php if (! $chart) continue; ?>
            <li role="presentation">
                <a href="#tabpanel-<?= $areaScope ?>-linechart" aria-controls="tabpanel-<?= $areaScope ?>-linechart" role="tab" data-toggle="tab">
                    <?= $areas[$areaScope] ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="tab-content">
        <?php foreach($lineChart as $areaScope => $chart): ?>
            <?php if (! $chart) continue; ?>
            <div role="tabpanel" class="tab-pane active" id="tabpanel-<?= $areaScope ?>-linechart">
                <?php $lineChart[$areaScope]->div($areaScope.'_line_chart_container'); ?>
                <div id="<?= $areaScope ?>_line_chart_container"></div>
                <?php $this->GoogleCharts->createJsChart($lineChart[$areaScope]); ?>
            </div>
        <?php endforeach; ?>
    </div>

    <p>
        <strong>Note: </strong> Recessions were declared by NBER during the following periods:
    </p>
    <ul>
        <li>Nov. 1973 - March 1975,</li>
        <li>Jan. 1980 - July 1980,</li>
        <li>July 1981 - Nov. 1982,</li>
        <li>July 1990 - March 1991,</li>
        <li>March 2001 - Nov. 2001,</li>
        <li>Dec. 2007 - June 2009</li>
    </ul>

    <p class="footnote">
        Data Source: Author calculations and Bureau of Economic Analysis
    </p>
</section>

<section class="community_data_section">
    <h2>
        Change in Employment, 2006 and 2011
    </h2>
    <p>
        This table shows area employment before and after the 2007-2009 recession.
    </p>

    <ul class="nav nav-tabs" role="tablist">
        <?php foreach($growthTable as $areaScope => $table): ?>
            <?php if (! $table) continue; ?>
            <li role="presentation">
                <a href="#tabpanel-<?= $areaScope ?>-growthtable" aria-controls="tabpanel-<?= $areaScope ?>-growthtable" role="tab" data-toggle="tab">
                    <?= $areas[$areaScope] ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="tab-content">
        <?php foreach($growthTable as $areaScope => $table): ?>
            <?php if (! $table) continue; ?>
            <div role="tabpanel" class="tab-pane active" id="tabpanel-<?= $areaScope ?>-growthtable">
                <?= $this->element('Communities'.DS.'growth_table', compact('table')) ?>
            </div>
        <?php endforeach; ?>
    </div>

    <p class="footnote">
        Data Source: Author calculations and Bureau of Economic Analysis
    </p>
</section>

<?php $this->append('buffered'); ?>
    $('.community_data_section .nav-tabs li:first-child a').tab('show');
<?php $this->end(); ?>
