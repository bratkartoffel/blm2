/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
package eu.fraho.blm2.st;

import io.github.bonigarcia.wdm.WebDriverManager;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.firefox.FirefoxDriver;
import org.openqa.selenium.firefox.FirefoxOptions;
import org.openqa.selenium.firefox.FirefoxProfile;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.time.Duration;

public class SeleniumConfig {
    private static final Logger log = LoggerFactory.getLogger(SeleniumConfig.class);

    public static WebDriver getWebDriver() {
        log.info("Using browser binary at {}", System.getProperty("webdriver.firefox.bin"));
        WebDriverManager.firefoxdriver().setup();
        FirefoxOptions options = new FirefoxOptions()
                .setProfile(new FirefoxProfile())
                .addArguments("-headless")
                ;
        WebDriver driver = new FirefoxDriver(options);
        driver.manage().timeouts().implicitlyWait(Duration.ofSeconds(3));
        driver.manage().window().maximize();
        return driver;
    }
}
