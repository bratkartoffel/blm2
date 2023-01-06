/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
package eu.fraho.blm2.st;

import org.junit.jupiter.api.AfterAll;
import org.junit.jupiter.api.Assertions;
import org.junit.jupiter.api.BeforeAll;
import org.junit.jupiter.api.BeforeEach;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.time.Duration;

public abstract class AbstractTest {
    private static final Logger log = LoggerFactory.getLogger(AbstractTest.class);

    private static WebDriver driver;

    @BeforeAll
    static void setupDriver() {
        driver = SeleniumConfig.getWebDriver();
    }

    @AfterAll
    static void teardownDriver() {
        driver.quit();
    }

    @BeforeEach
    void reset() {
        driver.manage().deleteAllCookies();
        driver.get(String.format("http://localhost/?_=%d", System.currentTimeMillis()));
        new WebDriverWait(driver, Duration.ofSeconds(1), Duration.ofMillis(100)).until(ExpectedConditions.titleContains("TST"));
    }

    protected static WebDriver getDriver() {
        return driver;
    }

    protected void login(WebDriver driver, String username) {
        login(driver, username, "changeit");
        new WebDriverWait(driver, Duration.ofSeconds(5)).until(ExpectedConditions.visibilityOfElementLocated(By.id("link_logout")));
    }

    protected void login(WebDriver driver, String username, String password) {
        log.info("Logging in as {}:{}", username, password);
        driver.findElement(By.id("link_anmelden")).click();
        WebElement inhalt = driver.findElement(By.id("Inhalt"));
        inhalt.findElement(By.id("name")).sendKeys(username);
        inhalt.findElement(By.id("pwd")).sendKeys(password);
        inhalt.findElement(By.id("login")).submit();
    }

    protected void assertElementPresent(By by) {
        Assertions.assertEquals(1, getDriver().findElements(by).size(), "Element " + by + " not found");
    }
}
