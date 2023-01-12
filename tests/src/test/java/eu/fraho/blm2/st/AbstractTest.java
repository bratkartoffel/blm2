/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
package eu.fraho.blm2.st;

import com.evanlennick.retry4j.CallExecutor;
import com.evanlennick.retry4j.CallExecutorBuilder;
import com.evanlennick.retry4j.config.RetryConfig;
import com.evanlennick.retry4j.config.RetryConfigBuilder;
import com.evanlennick.retry4j.exception.RetriesExhaustedException;
import com.evanlennick.retry4j.exception.UnexpectedException;
import org.junit.jupiter.api.Assertions;
import org.junit.jupiter.api.BeforeAll;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.TestInfo;
import org.openqa.selenium.By;
import org.openqa.selenium.Keys;
import org.openqa.selenium.NoSuchElementException;
import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.opentest4j.AssertionFailedError;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.io.IOException;
import java.lang.reflect.Method;
import java.net.URI;
import java.net.URLEncoder;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.nio.charset.StandardCharsets;
import java.time.temporal.ChronoUnit;
import java.util.List;
import java.util.Optional;
import java.util.concurrent.atomic.AtomicBoolean;

public abstract class AbstractTest {
    static final String RANDOM_TOKEN = "07313f0e320f22cbfa35cfc220508eb3ff457c7e";

    private static final Logger log = LoggerFactory.getLogger(AbstractTest.class);

    private static final WebDriver driver = SeleniumConfig.getWebDriver();

    private static final AtomicBoolean installed = new AtomicBoolean();

    @BeforeEach
    void resetDriver(TestInfo testInfo) {
        driver.manage().deleteAllCookies();
        driver.get("http://localhost/?test=" + URLEncoder.encode("%s_%s".formatted(
                        testInfo.getTestClass().map(Class::getName).orElse(null),
                        testInfo.getTestMethod().map(Method::getName).orElse(null)
                ), StandardCharsets.UTF_8)
        );
        driver.findElement(By.id("Inhalt"));

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
        RetryConfig config = new RetryConfigBuilder()
                .retryOnSpecificExceptions(TimeoutException.class, NoSuchElementException.class)
                .withMaxNumberOfTries(3)
                .withDelayBetweenTries(3, ChronoUnit.SECONDS)
                .withFixedBackoff()
                .build();
        try {
            @SuppressWarnings({"unchecked", "rawtypes"})
            CallExecutor<Boolean> executor = new CallExecutorBuilder().config(config).build();
            executor.execute(() -> {
                login(username, "changeit");
                driver.findElement(By.id("meldung_202"));
                return true;
            });
        } catch (RetriesExhaustedException | UnexpectedException e) {
            Assertions.fail(e);
        }
    }

    protected void login(String username, String password) {
        log.info("Logging in as {}:{}", username, password);
        driver.get("http://localhost/actions/logout.php");
        driver.findElement(By.id("link_anmelden")).click();
        WebElement inhalt = driver.findElement(By.id("Inhalt"));
        inhalt.findElement(By.id("name")).sendKeys(username);
        inhalt.findElement(By.id("pwd")).sendKeys(password);
        inhalt.findElement(By.id("login")).submit();
    }

    protected void assertElementPresent(By by) {
        try {
            Assertions.assertEquals(1, driver.findElements(by).size(), "Element " + by + " not found");
        } catch (AssertionFailedError e) {
            if (by.toString().contains("meldung_")) {
                List<WebElement> messageBox = driver.findElements(By.className("MessageBox"));
                if (!messageBox.isEmpty()) {
                    log.error("Message {} not found, but {} is beeing shown", by, messageBox.get(0).getDomAttribute("id"));
                }
            }
            throw e;
        }
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
