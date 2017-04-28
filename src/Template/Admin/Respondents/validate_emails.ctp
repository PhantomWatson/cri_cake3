<h1>
    Invalid email addresses in <em>respondents</em> table:
</h1>

<?php if ($invalidEmails): ?>
    <ul>
        <?php foreach ($invalidEmails as $invalidEmail): ?>
            <li>
                <?= $invalidEmail ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>
        (none)
    </p>
<?php endif; ?>
