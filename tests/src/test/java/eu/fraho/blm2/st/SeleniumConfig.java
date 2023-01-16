/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
package eu.fraho.blm2.st;

import io.github.bonigarcia.wdm.WebDriverManager;
import org.junit.jupiter.api.condition.OS;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.firefox.FirefoxDriver;
import org.openqa.selenium.firefox.FirefoxOptions;
import org.openqa.selenium.firefox.FirefoxProfile;

import java.time.Duration;

public class SeleniumConfig {
    private static WebDriver driver;

    public static synchronized WebDriver getWebDriver() {
        if (driver == null) {
            System.out.println("Starting " + System.getProperty("webdriver.firefox.bin"));
            WebDriverManager.firefoxdriver().setup();
            FirefoxOptions options = new FirefoxOptions()
                    .setProfile(new FirefoxProfile())
                    .addPreference("intl.accept_languages", "en-US, en");
            if (!OS.WINDOWS.isCurrentOs()) {
                options.addArguments("-headless");
            }
            driver = new FirefoxDriver(options);
            driver.manage().timeouts().implicitlyWait(Duration.ofSeconds(3));
            Runtime.getRuntime().addShutdownHook(new Thread(driver::quit));
        }
        return driver;
    }
}
