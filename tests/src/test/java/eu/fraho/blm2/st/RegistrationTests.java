/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
package eu.fraho.blm2.st;

import org.junit.jupiter.api.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;

class RegistrationTests extends AbstractTest {
    @Test
    void testEmailDuplicate() {
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_registrieren")).click();
        WebElement inhalt = driver.findElement(By.id("Inhalt"));
        inhalt.findElement(By.id("name")).sendKeys("testEmailDuplicate");
        inhalt.findElement(By.id("email")).sendKeys("test1@example.com");
        inhalt.findElement(By.id("pwd1")).sendKeys("changeit");
        inhalt.findElement(By.id("pwd2")).sendKeys("changeit");
        inhalt.findElement(By.id("captcha_code")).sendKeys("123456");
        inhalt.findElement(By.id("register")).submit();
        assertElementPresent(By.id("meldung_106"));
    }

    @Test
    void testPasswordNoMatch() {
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_registrieren")).click();
        WebElement inhalt = driver.findElement(By.id("Inhalt"));
        inhalt.findElement(By.id("name")).sendKeys("testPasswordNoMatch");
        inhalt.findElement(By.id("email")).sendKeys("testPasswordNoMatch@example.com");
        inhalt.findElement(By.id("pwd1")).sendKeys("changeit");
        inhalt.findElement(By.id("pwd2")).sendKeys("test1234");
        inhalt.findElement(By.id("captcha_code")).sendKeys("123456");
        inhalt.findElement(By.id("register")).submit();
        assertElementPresent(By.id("meldung_105"));
    }

    @Test
    void testPasswordTooShort() {
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_registrieren")).click();
        WebElement inhalt = driver.findElement(By.id("Inhalt"));
        inhalt.findElement(By.id("name")).sendKeys("testPasswordTooShort");
        inhalt.findElement(By.id("email")).sendKeys("testPasswordTooShort@example.com");
        inhalt.findElement(By.id("pwd1")).sendKeys("abc");
        inhalt.findElement(By.id("pwd2")).sendKeys("abc");
        inhalt.findElement(By.id("captcha_code")).sendKeys("123456");
        inhalt.findElement(By.id("register")).submit();
        assertElementPresent(By.id("meldung_147"));
    }

    @Test
    void testUsernameDuplicate() {
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_registrieren")).click();
        WebElement inhalt = driver.findElement(By.id("Inhalt"));
        inhalt.findElement(By.id("name")).sendKeys("test1");
        inhalt.findElement(By.id("email")).sendKeys("testUsernameDuplicate@example.com");
        inhalt.findElement(By.id("pwd1")).sendKeys("changeit");
        inhalt.findElement(By.id("pwd2")).sendKeys("changeit");
        inhalt.findElement(By.id("captcha_code")).sendKeys("123456");
        inhalt.findElement(By.id("register")).submit();
        assertElementPresent(By.id("meldung_106"));
    }

    @Test
    void testUsernameTooShort() {
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_registrieren")).click();
        WebElement inhalt = driver.findElement(By.id("Inhalt"));
        inhalt.findElement(By.id("name")).sendKeys("a");
        inhalt.findElement(By.id("email")).sendKeys("testUsernameTooShort@example.com");
        inhalt.findElement(By.id("pwd1")).sendKeys("changeit");
        inhalt.findElement(By.id("pwd2")).sendKeys("changeit");
        inhalt.findElement(By.id("captcha_code")).sendKeys("123456");
        inhalt.findElement(By.id("register")).submit();
        assertElementPresent(By.id("meldung_146"));
    }

    @Test
    void testRegistrationSuccess() {
        String username = "test-" + System.currentTimeMillis();
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_registrieren")).click();
        WebElement inhalt = driver.findElement(By.id("Inhalt"));
        inhalt.findElement(By.id("name")).sendKeys(username);
        inhalt.findElement(By.id("email")).sendKeys(username + "@example.com");
        inhalt.findElement(By.id("pwd1")).sendKeys("changeit");
        inhalt.findElement(By.id("pwd2")).sendKeys("changeit");
        inhalt.findElement(By.id("captcha_code")).sendKeys("123456");
        inhalt.findElement(By.id("register")).submit();
        assertElementPresent(By.id("meldung_201"));

        // verify that user cannot login
        login(driver, username, "changeit");
        assertElementPresent(By.id("meldung_135"));

        // wrong activation token
        driver.get("http://localhost/actions/activate.php?user=" + username + "&code=x7313f0e320f22cbfa35cfc220508eb3ff457c7e");
        assertElementPresent(By.id("meldung_117"));

        // correct activation token
        driver.get("http://localhost/actions/activate.php?user=" + username + "&code=07313f0e320f22cbfa35cfc220508eb3ff457c7e");
        assertElementPresent(By.id("meldung_241"));

        // verify that user can login now
        login(driver, username);
    }
}
