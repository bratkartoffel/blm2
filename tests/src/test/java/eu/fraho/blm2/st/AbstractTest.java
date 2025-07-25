/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
package eu.fraho.blm2.st;

import com.evanlennick.retry4j.CallExecutorBuilder;
import com.evanlennick.retry4j.config.RetryConfig;
import com.evanlennick.retry4j.config.RetryConfigBuilder;
import com.evanlennick.retry4j.exception.RetriesExhaustedException;
import com.evanlennick.retry4j.exception.UnexpectedException;
import com.fasterxml.jackson.core.type.TypeReference;
import com.fasterxml.jackson.databind.ObjectMapper;
import org.hamcrest.MatcherAssert;
import org.hamcrest.Matchers;
import org.junit.jupiter.api.Assertions;
import org.junit.jupiter.api.BeforeAll;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.TestInfo;
import org.openqa.selenium.*;
import org.openqa.selenium.NoSuchElementException;
import org.opentest4j.AssertionFailedError;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.io.IOException;
import java.lang.reflect.Method;
import java.math.BigDecimal;
import java.net.URI;
import java.net.URLEncoder;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.nio.charset.StandardCharsets;
import java.time.Duration;
import java.time.LocalDateTime;
import java.time.temporal.ChronoUnit;
import java.util.*;
import java.util.concurrent.atomic.AtomicBoolean;
import java.util.concurrent.atomic.AtomicInteger;
import java.util.regex.Pattern;

public abstract class AbstractTest {
    public static final String RANDOM_TOKEN = "07313f0e320f22cbfa35cfc220508eb3ff457c7e";
    private static final AtomicInteger USER_ID = new AtomicInteger(
            // 31 days, 24 hours, 60 minutes, 60 seconds = 2_678_400 seconds per month; divided by 3 results a value at most 892_800
            // As gruppe.Kuerzel is a varchar(6), this is perfectly fine
            (int) (Duration.between(LocalDateTime.now().withDayOfMonth(1).withHour(0).withMinute(0).withSecond(0), LocalDateTime.now()).toSeconds() / 3)
    );
    public static final String INBUCKET_HOST = "localhost:9000";
    public static final String BASE_URL = "http://localhost:8080/blm2";
    private static final Logger log = LoggerFactory.getLogger(AbstractTest.class);
    private static final WebDriver driver = SeleniumConfig.getWebDriver();
    private static final HttpClient httpClient = HttpClient.newHttpClient();
    private static final AtomicBoolean installed = new AtomicBoolean();
    private static final ObjectMapper objectMapper = new ObjectMapper().findAndRegisterModules();

    @BeforeEach
    void resetDriver(TestInfo testInfo) {
        driver.manage().deleteAllCookies();
        driver.get("%s/?test=%s".formatted(BASE_URL, URLEncoder.encode("%s_%s".formatted(
                        testInfo.getTestClass().map(Class::getName).orElse(null),
                        testInfo.getTestMethod().map(Method::getName).orElse(null)
                ), StandardCharsets.UTF_8))
        );
        driver.findElement(By.id("Inhalt"));
    }

    @BeforeAll
    static void install() {
        if (installed.compareAndSet(false, true)) {
            try {
                HttpResponse<String> response = simpleHttpGet("%s/install/update.php?secret=changeit".formatted(BASE_URL));
                if (response.statusCode() != 200) {
                    Assertions.fail(response.body());
                }
                Arrays.stream(response.body().split("\n")).forEach(l -> log.info("Installer: {}", l));
            } catch (IOException | InterruptedException e) {
                driver.quit();
                Assertions.fail(e);
            }
        }
    }

    protected static int getNextUserId() {
        return USER_ID.getAndIncrement();
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
            //noinspection unchecked
            new CallExecutorBuilder<Boolean>().config(config).build().execute(() -> {
                this.login(username, "changeit");
                driver.findElement(By.id("meldung_202"));
                return null;
            }, "login");
        } catch (RetriesExhaustedException | UnexpectedException e) {
            Assertions.fail(e);
        }
    }

    protected void login(String username, String password) {
        log.info("Logging in as {}:{}", username, password);
        driver.get("%s/actions/logout.php".formatted(BASE_URL));
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

    protected void assertTextMatches(By by, String expected) {
        MatcherAssert.assertThat(getDriver().findElement(by).getText(), Matchers.matchesPattern(Pattern.compile(expected, Pattern.MULTILINE | Pattern.DOTALL)));
    }

    protected void assertValue(By by, BigDecimal expected) {
        Assertions.assertEquals(expected, new BigDecimal(getDriver().findElement(by).getAttribute("value")));
    }

    protected void setValue(By by, String value) {
        WebElement element = driver.findElement(by);
        element.clear();
        element.sendKeys(value, Keys.TAB);
    }

    protected void selectByText(By by, String text) {
        for (WebElement element : driver.findElement(by).findElements(By.tagName("option"))) {
            if (Objects.equals(element.getText(), text)) {
                element.click();
                break;
            }
        }
    }

    protected void selectByValue(By by, String value) {
        for (WebElement element : driver.findElement(by).findElements(By.tagName("option"))) {
            if (Objects.equals(element.getAttribute("value"), value)) {
                element.click();
                break;
            }
        }
    }

    protected void setCheckbox(By by, boolean state) {
        WebDriver driver = getDriver();
        WebElement element = driver.findElement(by);
        if (element.isSelected() != state) {
            element.click();
        }
    }

    protected void resetPlayer(int id, TestInfo testInfo) {
        resetPlayer(id, testInfo, null);
    }

    protected void resetPlayer(int id, TestInfo testInfo, Integer additionInfo) {
        HttpResponse<String> response = null;
        try {
            String testClass = testInfo.getTestClass().map(Class::getSimpleName).orElse(null);
            String testMethod = testInfo.getTestMethod().map(Method::getName).orElse(null);
            response = simpleHttpGet("%s/actions/test-reset-player.php?id=%d&class=%s&method=%s&additional=%d".formatted(BASE_URL, id, testClass, testMethod, additionInfo));
            Optional<String> location = response.headers().firstValue("Location");
            Assertions.assertTrue(location.isPresent());
            Assertions.assertEquals("/blm2/actions/logout.php", location.get());
        } catch (IOException | InterruptedException e) {
            Assertions.fail(e);
        } catch (AssertionFailedError e) {
            log.warn(Optional.ofNullable(response).map(HttpResponse::body).orElse("no body"));
            throw e;
        }
    }

    protected void resetPlayer(int id, String testClass) {
        try {
            HttpResponse<String> response = simpleHttpGet("%s/actions/test-reset-player.php?id=%d&class=%s".formatted(BASE_URL, id, testClass));
            Optional<String> location = response.headers().firstValue("Location");
            Assertions.assertTrue(location.isPresent());
            Assertions.assertEquals("/blm2/actions/logout.php", location.get());
        } catch (IOException | InterruptedException e) {
            Assertions.fail(e);
        }
    }

    protected Map<String, ?> getLatestMail(String mailbox) {
        try {
            HttpResponse<String> response = simpleHttpGet("http://%s/api/v1/mailbox/%s".formatted(INBUCKET_HOST, mailbox));
            List<Map<String, ?>> value = objectMapper.readValue(response.body(), new TypeReference<>() {
            });
            Assertions.assertFalse(value.isEmpty());
            String id = (String) value.get(value.size() - 1).get("id");
            response = simpleHttpGet("http://%s/api/v1/mailbox/%s/%s".formatted(INBUCKET_HOST, mailbox, id));
            return objectMapper.readValue(response.body(), new TypeReference<>() {
            });
        } catch (IOException | InterruptedException e) {
            Assertions.fail(e);
            return Collections.emptyMap();
        }
    }

    protected void runCronjob() {
        runCronjob(1);
    }

    protected void runCronjob(int count) {
        HttpClient httpClient = HttpClient.newHttpClient();
        try {
            HttpResponse<String> response = httpClient.send(
                    HttpRequest.newBuilder().GET().uri(URI.create("%s/actions/test-run-cron.php?count=%d".formatted(BASE_URL, count))).build(),
                    HttpResponse.BodyHandlers.ofString()
            );
            Assertions.assertEquals(200, response.statusCode());
            Arrays.stream(response.body().split("\n")).forEach(l -> log.info("Cronjob: {}", l));
        } catch (IOException | InterruptedException e) {
            Assertions.fail(e);
        }
    }

    protected static HttpResponse<String> simpleHttpGet(String url) throws IOException, InterruptedException {
        log.info("Calling {}", url);
        return httpClient.send(
                HttpRequest.newBuilder().GET().uri(URI.create(url)).build(),
                HttpResponse.BodyHandlers.ofString()
        );
    }
}
