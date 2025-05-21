<div class="steps">
    <?php
    $steps = [
        1 => 'Infos pers.',
        2 => 'Poste',
        3 => 'Entreprise',
        4 => 'Documents',
        5 => 'Validation'
    ];

    foreach ($steps as $num => $label) {
        $classes = 'step';
        if ($currentStep == $num) $classes .= ' active';
        elseif ($currentStep > $num) $classes .= ' completed';

        echo "<div class=\"$classes\">";
        if ($currentStep > $num) {
            echo "<span class=\"checkmark\">✓</span>";
        } else {
            echo "<span>$num</span>";
        }
        echo "<div class=\"label\">$label</div>";
        echo "</div>";
    }
    ?>
</div>

<style>

    .steps {
    display: flex;
    justify-content: space-between;
    max-width: 700px;
    margin: 2rem auto;
    padding: 0;
    gap: 10px;
}

.step {
    text-align: center;
    flex: 1;
    font-size: 14px;
    color: #666;
}

.step span {
    display: inline-block;
    background: #ddd;
    color: #333;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    line-height: 28px;
    font-weight: bold;
    margin-bottom: 5px;
}

.step .checkmark {
    background: #28a745;
    color: white;
    font-size: 16px;
    width: 28px;
    height: 28px;
    display: inline-block;
    line-height: 28px;
    border-radius: 50%;
}

.step .label {
    margin-top: 4px;
}

.step.active span {
    background: #007bff;
    color: white;
}

.step.completed .label {
    font-weight: bold;
    color:rgb(37, 103, 53);
}

</style>