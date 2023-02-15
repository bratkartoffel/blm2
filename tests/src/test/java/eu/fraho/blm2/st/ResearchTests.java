/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
package eu.fraho.blm2.st;

import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.TestInfo;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;

public class ResearchTests extends AbstractTest {
    private final int userId = getNextUserId();

    @BeforeEach
    void beforeEach(TestInfo testInfo) {
        resetPlayer(userId, testInfo);
        login("test" + userId);
    }

    @Test
    void testNotBuilt() {
        WebDriver driver = getDriver();

        driver.findElement(By.id("link_forschungszentrum")).click();
        assertElementPresent(By.id("meldung_145"));
        assertElementPresent(By.id("build_2"));
    }

    @Test
    void testResearchAndCancel() {
        WebDriver driver = getDriver();

        driver.findElement(By.id("link_forschungszentrum")).click();

        assertText(By.id("stat_money"), "15,000.00 €");
        driver.findElement(By.id("research_1")).click();
        assertElementPresent(By.id("meldung_207"));
        assertElementPresent(By.id("abort_1"));

        assertText(By.id("stat_money"), "4,617.26 €");
        driver.findElement(By.id("abort_1")).click();
        driver.switchTo().alert().accept();
        assertElementPresent(By.id("meldung_222"));

        assertElementPresent(By.id("research_1"));
        assertText(By.id("stat_money"), "12,404.32 €");

        driver.findElement(By.id("link_buero")).click();
        assertText(By.id("b_s_2"), "2,595.68 €");
    }
}
