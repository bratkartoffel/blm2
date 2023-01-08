/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
package eu.fraho.blm2.st;

import org.junit.jupiter.api.Assertions;
import org.junit.jupiter.api.BeforeAll;
import org.junit.jupiter.api.BeforeEach;
import org.openqa.selenium.By;
import org.openqa.selenium.Keys;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.io.IOException;
import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.time.Duration;
import java.util.Optional;
import java.util.concurrent.atomic.AtomicBoolean;

public abstract class AbstractTest {
    private static final Logger log = LoggerFactory.getLogger(AbstractTest.class);

    private static final WebDriver driver = SeleniumConfig.getWebDriver();

    private static final AtomicBoolean installed = new AtomicBoolean();

    @BeforeEach
    void resetDriver() {
        driver.manage().deleteAllCookies();
        driver.get(String.format("http://localhost/?_=%d", System.currentTimeMillis()));
        new WebDriverWait(driver, Duration.ofSeconds(1), Duration.ofMillis(100)).until(ExpectedConditions.titleContains("TST"));
    }

    @BeforeAll
    static void install() {
        if (installed.compareAndSet(false, true)) {
            HttpClient httpClient = HttpClient.newHttpClient();
            try {
                HttpResponse<String> response = httpClient.send(
                        HttpRequest.newBuilder().GET().uri(URI.create("http://localhost/install/update.php?secret=changeit")).build(),
                        HttpResponse.BodyHandlers.ofString()
                );
                if (response.statusCode() != 200) {
                    Assertions.fail(response.body());
                }
            } catch (IOException | InterruptedException e) {
                Assertions.fail(e);
            }
        }
    }

    protected static WebDriver getDriver() {
        return driver;
    }

    protected void login(String username) {
        login(username, "changeit");
        new WebDriverWait(driver, Duration.ofSeconds(5)).until(ExpectedConditions.visibilityOfElementLocated(By.id("link_logout")));
    }

    protected void login(String username, String password) {
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

    protected void assertText(By by, String expected) {
        Assertions.assertEquals(expected, getDriver().findElement(by).getText());
    }

    protected void setValue(By by, String value) {
        WebElement element = driver.findElement(by);
        element.clear();
        element.sendKeys(value, Keys.TAB);
    }

    protected void select(By by, String label) {
        driver.findElement(by).findElement(By.xpath("//option[. = '%s']".formatted(label))).click();
    }

    protected void resetPlayer(int id) {
        HttpClient httpClient = HttpClient.newHttpClient();
        try {
            HttpResponse<String> response = httpClient.send(
                    HttpRequest.newBuilder().GET().uri(URI.create("http://localhost/actions/test-reset-player.php?id=" + id)).build(),
                    HttpResponse.BodyHandlers.ofString()
            );
            Optional<String> location = response.headers().firstValue("Location");
            Assertions.assertTrue(location.isPresent());
            Assertions.assertEquals("/actions/logout.php", location.get());
        } catch (IOException | InterruptedException e) {
            Assertions.fail(e);
        }
    }
}
