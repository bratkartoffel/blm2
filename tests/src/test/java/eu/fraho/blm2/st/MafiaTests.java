/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
package eu.fraho.blm2.st;

import org.junit.jupiter.api.Assertions;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.TestInfo;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;

import java.util.List;

public class MafiaTests extends AbstractTest {
    private final int userId0 = getNextUserId();
    private final int userId1 = getNextUserId();
    private final int userId2 = getNextUserId();
    private final int userId3 = getNextUserId();

    @BeforeEach
    void beforeEach(TestInfo testInfo) {
        resetPlayer(userId0, testInfo, 0);
        resetPlayer(userId1, testInfo, 1);
        resetPlayer(userId2, testInfo, 2);
        resetPlayer(userId3, testInfo, 3);
    }

    @Test
    void testMafiaNotActive() {
        login("test" + userId0);
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_mafia")).click();
        assertElementPresent(By.id("meldung_169"));
    }

    @Test
    void testLowerCanAttackMiddle() {
        login("test" + userId1);
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_mafia")).click();

        List<WebElement> options = driver.findElement(By.id("opponent")).findElements(By.tagName("option"));
        for (WebElement option : options) {
            switch (Integer.parseInt(option.getAttribute("value")) - userId0) {
                case 0:
                    Assertions.fail("Can attack player without mafia");
                    break;
                case 1:
                    Assertions.fail("Can attack self");
                    break;
                case 2:
                    // all good
                    break;
                case 3:
                    Assertions.fail("Can attack out of range");
                    break;
                default:
                    // ignore
                    break;
            }
        }
    }

    @Test
    void testHigherCanAttackMiddle() {
        login("test" + userId3);
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_mafia")).click();

        List<WebElement> options = driver.findElement(By.id("opponent")).findElements(By.tagName("option"));
        for (WebElement option : options) {
            switch (Integer.parseInt(option.getAttribute("value")) - userId0) {
                case 0:
                    Assertions.fail("Can attack player without mafia");
                    break;
                case 1:
                    Assertions.fail("Can attack out of range");
                    break;
                case 2:
                    // all good
                    break;
                case 3:
                    Assertions.fail("Can attack self");
                    break;
                default:
                    // ignore
                    break;
            }
        }
    }

    @Test
    void testMiddleCanAttackBoth() {
        login("test" + userId2);
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_mafia")).click();

        List<WebElement> options = driver.findElement(By.id("opponent")).findElements(By.tagName("option"));
        for (WebElement option : options) {
            switch (Integer.parseInt(option.getAttribute("value")) - userId0) {
                case 0:
                    Assertions.fail("Can attack player without mafia");
                    break;
                case 1:
                    // all good
                    break;
                case 2:
                    Assertions.fail("Can attack self");
                    break;
                case 3:
                    // all good
                default:
                    // ignore
                    break;
            }
        }
    }
}
