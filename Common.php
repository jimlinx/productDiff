<?php

foreach (glob("Utility/*.php") as $filename)
{
    include $filename;
}

foreach (glob("Logic/*.php") as $filename)
{
    include $filename;
}
?>