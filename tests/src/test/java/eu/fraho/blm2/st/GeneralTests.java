/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
package eu.fraho.blm2.st;

import org.junit.jupiter.api.Assertions;
import org.junit.jupiter.params.ParameterizedTest;
import org.junit.jupiter.params.provider.ValueSource;

import java.io.IOException;
import java.net.http.HttpResponse;

public class GeneralTests extends AbstractTest {
    @ParameterizedTest
    @ValueSource(strings = {
            ".git/HEAD",
            "config/config.ini",
            "cronjobs/cron.php",
            "development/watchers.xml",
            "include/captcha.class.php",
            "install/sql/00-1.10.0-setup.sql",
            "install/update.php",
            "install/update.php?secret=wrong",
            "pages/admin.inc.php",
            "pics/uploads/.gitkeep",
            "tests/build.gradle",
            "mails/email_change.html.tpl",
            "vendor/PHPMailer/src/VERSION",
    })
    void testSensitiveFilesInacessible(String path) throws IOException, InterruptedException {
        HttpResponse<String> response = simpleHttpGet("http://%s/%s".formatted(LOCALHOST, path));
        Assertions.assertEquals(4, response.statusCode() / 100);
    }
}
