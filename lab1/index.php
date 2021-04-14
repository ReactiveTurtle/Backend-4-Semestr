<?php
function isValidNumber($value)
{
    return isset($value) && is_numeric($value);
}

function calc($firstNumber, $secondNumber, $action)
{
    if (!isValidNumber($firstNumber)) {
        return "Error";
    }
    $firstNumber = (float)$firstNumber;

    if (!isValidNumber($secondNumber)) {
        return "Error";
    }
    $secondNumber = (float)$secondNumber;

    switch ($action) {
        case "+":
            return $firstNumber + $secondNumber;
        case "-":
            return $firstNumber - $secondNumber;
        case "*":
            return $firstNumber * $secondNumber;
        case "/":
            return $firstNumber / $secondNumber;
    }
    return "Error";
}

$firstNumber = isset($_POST["firstNumber"]) ? $_POST["firstNumber"] : 0;
$secondNumber = isset($_POST["secondNumber"]) ? $_POST["secondNumber"] : 0;
$action = isset($_POST["action"]) ? $_POST["action"] : "+";
$result = calc($firstNumber, $secondNumber, $action);
?>

<form method="post" action="/">
    <div>
        <label>
            <input type="text" name="firstNumber" value="<?php echo $firstNumber ?>"/>
        </label>
        <label>
            <select name="action">
                <option value="+" <?php echo $action == "+" ? "selected" : '' ?>>+</option>
                <option value="-" <?php echo $action == "-" ? "selected" : '' ?>>-</option>
                <option value="*" <?php echo $action == "*" ? "selected" : '' ?>>*</option>
                <option value="/" <?php echo $action == "/" ? "selected" : '' ?>>/</option>
            </select>
        </label>
        <label>
            <input type="text" name="secondNumber" value="<?php echo $secondNumber ?>"/>
        </label>
        <input type="submit" value="Calc"/>
    </div>
</form>
<label>Result: <?php echo $result; ?></label>
