/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
package eu.fraho.blm2.st;

import org.hamcrest.MatcherAssert;
import org.hamcrest.Matchers;
import org.junit.jupiter.api.Assertions;
import org.junit.jupiter.params.ParameterizedTest;
import org.junit.jupiter.params.provider.MethodSource;
import org.junit.jupiter.params.provider.ValueSource;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;

import java.io.IOException;
import java.net.http.HttpResponse;
import java.nio.file.Files;
import java.nio.file.Path;
import java.util.List;
import java.util.stream.Stream;

public class GeneralTests extends AbstractTest {
    private final int userIdUser = getNextUserId();

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
    void testSensitiveFilesInaccessible(String path) throws IOException, InterruptedException {
        HttpResponse<String> response = simpleHttpGet("%s/%s".formatted(AbstractTest.BASE_URL, path));
        Assertions.assertEquals(4, response.statusCode() / 100);
    }

    @ParameterizedTest
    @MethodSource("getAdminPages")
    void testRegularUserMayNotAccessAdmin(String page) {
        resetPlayer(userIdUser, getClass().getSimpleName());
        login("test" + userIdUser);

        WebDriver driver = getDriver();
        driver.get("%s/?p=%s".formatted(AbstractTest.BASE_URL, page));
        assertElementPresent(By.id("meldung_101"));
    }

    static List<String> getAdminPages() throws IOException {
        try (Stream<Path> files = Files.list(Path.of("../pages/"))) {
            List<String> pages = files.map(Path::getFileName)
                    .map(Path::toString)
                    .filter(s -> s.endsWith(".inc.php"))
                    .filter(s -> s.startsWith("admin"))
                    .map(s -> s.substring(0, s.length() - 8))
                    .toList();
            MatcherAssert.assertThat(pages.size(), Matchers.greaterThan(10));
            return pages;
        }
    }
}
