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
    void testEspionageSuccess() {
        login("test" + userId2);
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_mafia")).click();
        selectByText(By.id("opponent"), "test" + userId1);
        selectByValue(By.id("action"), "1");
        selectByValue(By.id("level"), "3");
        driver.findElement(By.id("attack")).submit();

        // after mafia action inbox is opened
        assertElementPresent(By.id("MessagesIn"));
        assertText(By.id("stat_money"), "4,200.00 €");
        driver.findElement(By.id("MessagesIn")).findElements(By.tagName("a")).get(0).click();

        assertText(By.id("sender"), "System");
        assertText(By.id("subject"), String.format("Mafia: Spionage gegen test%d erfolgreich", userId1));
        assertText(By.id("message"), String.format("Die Spionage gegen test%d war erfolgreich, hier die von uns in Erfahrung gebrachten Daten:\n" +
                                      "\n" +
                                      "Bargeld: 5,000.00 €\n" +
                                      "\n" +
                                      "Lagerstände:\n" +
                                      "* Kartoffeln: 100 kg\n" +
                                      "* Karotten: 50 kg\n" +
                                      "* Äpfel: 27 kg\n" +
                                      "\n" +
                                      "Gebäudelevel:\n" +
                                      "* Plantage: 1\n" +
                                      "\n" +
                                      "- Ihre Mafia -", userId1));
    }

    @Test
    void testEspionageFailure() {
        login("test" + userId2);
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_mafia")).click();
        selectByText(By.id("opponent"), "test" + userId3);
        selectByValue(By.id("action"), "1");
        selectByValue(By.id("level"), "3");
        driver.findElement(By.id("attack")).submit();

        // after mafia action inbox is opened
        assertElementPresent(By.id("MessagesIn"));
        assertText(By.id("stat_money"), "4,200.00 €");
        driver.findElement(By.id("MessagesIn")).findElements(By.tagName("a")).get(0).click();

        assertText(By.id("sender"), "System");
        assertText(By.id("subject"), String.format("Mafia: Spionage gegen test%d fehlgeschlagen", userId3));
        assertText(By.id("message"), "Die Spionage war leider nicht erfolgreich, die gegnerischen Wachen haben unsere Spitzel erkannt bevor diese irgendwelche relevanten Daten sammeln konnten.\n" +
                                     "\n" +
                                     "- Ihre Mafia -");
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
