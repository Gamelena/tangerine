<?php

passthru( "/opt/composer/bower/bin/bower install --allow-root");
passthru( "cp -R dojotoolkit/* bower_components/");
passthru( "cp -R bower_components/dojo-calendar/* bower_components/dojox/calendar/");

