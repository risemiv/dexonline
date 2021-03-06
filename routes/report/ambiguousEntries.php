<?php

User::mustHave(User::PRIV_EDIT);

$entries = Entry::loadAmbiguous();

Smart::assign('entries', $entries);
Smart::addResources('admin');
Smart::display('report/ambiguousEntries.tpl');
