<?php

$choices = get_field('choices', $poll->ID);
$total = get_poll_total_votes($poll->ID);
?>
<p><strong>Total votes: </strong><?= $total; ?></p>
<table border="1" cellpadding="5px">
    <thead>
        <tr>
            <th>Choice</th>
            <th>Votes</th>
            <th>Percentage</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($choices as $choice): ?>
            <tr>
                <td><?= $choice['text'] ?></td>
                <td><?= $choice['count'] ?></td>
                <td><?= get_percentage($choice['count'], $total); ?>%</td>
            </tr>
        <?php endforeach;?>
    </tbody>
</table>
