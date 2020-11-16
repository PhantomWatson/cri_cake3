### Every three minutes:
 - `/usr/bin/wget -O - -q -t 1 http://cri.cberdata.org/surveys/cron_import >/dev/null 2>&1`

### Every five minutes
 - `cd /home/okbvtfr/public_html/cri && php -d register_argc_argv=1 bin/cake.php queue runworker >/dev/null 2>&1`

### 15 minutes after every hour
 - `cd /home/okbvtfr/public_html/cri && php bin/cake.php auto_advance run >/dev/null 2>&1`

### Weekdays at beginning of the hour from 3pm to 11pm
 - `cd /home/okbvtfr/public_html/cri && php bin/cake.php delayed_jobs alert >/dev/null 2>&1`

### First
 - `cd /home/okbvtfr/public_html/cri && php -d register_argc_argv=1 bin/cake.php queue runworker`
