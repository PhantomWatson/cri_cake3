<?php
    if ($this->Paginator->hasPrev() || $this->Paginator->hasNext()) {
        echo '<ul class="pagination">';
    }
    if ($this->Paginator->hasPrev()) {
        echo $this->Paginator->prev(
            '< prev',
            ['tag' => 'li'],
            null,
            ['class' => 'prev disabled']
        );
    }
    echo $this->Paginator->numbers([
        'currentClass' => 'active',
        'currentTag' => 'a',
        'separator' => '',
        'tag' => 'li'
    ]);
    if ($this->Paginator->hasNext()) {
        echo $this->Paginator->next(
            'next >',
            ['tag' => 'li'],
            null,
            ['class' => 'next disabled']
        );
    }
    if ($this->Paginator->hasPrev() || $this->Paginator->hasNext()) {
        echo '</ul>';
    }
