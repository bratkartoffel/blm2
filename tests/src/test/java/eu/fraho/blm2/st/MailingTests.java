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
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.TestInfo;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;

import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;
import java.util.Map;

public class MailingTests extends AbstractTest {
    private final int userId1 = getNextUserId(); // registration
    private final int userId2 = getNextUserId(); // change email address
    private final int userId3 = getNextUserId(); // password recovery
    private final int userId4 = getNextUserId(); // password reset

    @BeforeEach
    void beforeEach() {
        getDriver().get("%s/actions/logout.php".formatted(AbstractTest.BASE_URL));
    }

    @Test
    void testRegistration() {
        String username = "test" + userId1;
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_registrieren")).click();
        WebElement inhalt = driver.findElement(By.id("Inhalt"));
        inhalt.findElement(By.id("name")).sendKeys(username);
        inhalt.findElement(By.id("email")).sendKeys("test" + userId1 + "@localhost");
        inhalt.findElement(By.id("pwd1")).sendKeys("changeit");
        inhalt.findElement(By.id("pwd2")).sendKeys("changeit");
        inhalt.findElement(By.id("captcha_code")).sendKeys("123456");
        inhalt.findElement(By.id("register")).submit();
        assertElementPresent(By.id("meldung_201"));

        Map<String, ?> mail = getLatestMail("test" + userId1);
        Assertions.assertEquals("Insert Name Here <contact-address@example.com>", mail.get("from"));
        Assertions.assertEquals("TST - Der Bioladenmanager 2: Registrierung", mail.get("subject"));

        String body = ((Map<String, String>) mail.get("body")).get("text");
        MatcherAssert.assertThat(body, Matchers.containsString("%s/actions/activate.php?user=%s&code=%s".formatted(AbstractTest.BASE_URL, username, RANDOM_TOKEN)));
    }

    @Test
    void testChangeEmailAdress(TestInfo testInfo) {
        String username = "test" + userId2;
        String email = "MailingTests_c_" + userId2 + "@localhost";
        resetPlayer(userId2, testInfo);
        login(username);

        WebDriver driver = getDriver();
        driver.findElement(By.id("link_einstellungen")).click();

        setValue(By.id("email"), email);
        setValue(By.id("confirm"), email);
        driver.findElement(By.id("changeEmail")).submit();
        assertElementPresent(By.id("meldung_238"));

        Map<String, ?> mail = getLatestMail("MailingTests_c_" + userId2);
        Assertions.assertEquals("Insert Name Here <contact-address@example.com>", mail.get("from"));
        Assertions.assertEquals("TST - Der Bioladenmanager 2: Änderung EMail-Addresse", mail.get("subject"));

        String body = ((Map<String, String>) mail.get("body")).get("text");
        MatcherAssert.assertThat(body, Matchers.containsString("%s/actions/activate.php?email=%s&code=%s".formatted(AbstractTest.BASE_URL, URLEncoder.encode(email, StandardCharsets.UTF_8), RANDOM_TOKEN)));
    }

    @Test
    void testPasswordReset(TestInfo testInfo) {
        String email = getClass().getSimpleName() + "_" + userId3 + "@localhost";
        resetPlayer(userId3, testInfo);

        WebDriver driver = getDriver();
        driver.findElement(By.id("link_anmelden")).click();
        driver.findElement(By.id("link_passwort_vergessen")).click();

        setValue(By.id("email"), email);
        setValue(By.id("captcha_code"), "123456");

        driver.findElement(By.id("forgot_password")).submit();
        assertElementPresent(By.id("meldung_244"));

        Map<String, ?> mail = getLatestMail(email);
        Assertions.assertEquals("Insert Name Here <contact-address@example.com>", mail.get("from"));
        Assertions.assertEquals("TST - Der Bioladenmanager 2: Passwort vergessen", mail.get("subject"));

        String body = ((Map<String, String>) mail.get("body")).get("text");
        MatcherAssert.assertThat(body, Matchers.containsString("%s/actions/pwd_reset.php?a=2&id=%d&token=%s".formatted(AbstractTest.BASE_URL, userId3, RANDOM_TOKEN)));
    }

    @Test
    void testPasswordRecovery(TestInfo testInfo) {
        String username = "test" + userId4;
        String email = "MailingTests_" + userId4 + "@localhost";
        resetPlayer(userId4, testInfo);

        WebDriver driver = getDriver();
        driver.get("%s/actions/pwd_reset.php?a=2&id=%d&token=%s".formatted(AbstractTest.BASE_URL, userId4, RANDOM_TOKEN));
        assertElementPresent(By.id("meldung_245"));

        Map<String, ?> mail = getLatestMail(email);
        Assertions.assertEquals("Insert Name Here <contact-address@example.com>", mail.get("from"));
        Assertions.assertEquals("TST - Der Bioladenmanager 2: Dein neues Passwort", mail.get("subject"));

        String body = ((Map<String, String>) mail.get("body")).get("text");
        MatcherAssert.assertThat(body, Matchers.containsString("changeit"));

        login(username);
    }
}
