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

public class ResearchTests extends AbstractTest {
    @Test
    void testNotBuilt() {
        resetPlayer(11);
        login("test1");
        WebDriver driver = getDriver();

        driver.findElement(By.id("link_forschungszentrum")).click();
        assertElementPresent(By.id("meldung_145"));
        assertElementPresent(By.id("build_2"));
    }

    @Test
    void testResearchAndCancel() {
        resetPlayer(12);
        login("test2");
        WebDriver driver = getDriver();

        driver.findElement(By.id("link_forschungszentrum")).click();

        assertText(By.id("stat_money"), "5.000,00 €");
        driver.findElement(By.id("research_1")).click();
        assertElementPresent(By.id("meldung_207"));
        assertElementPresent(By.id("abort_1"));

        assertText(By.id("stat_money"), "4.406,26 €");
        driver.findElement(By.id("abort_1")).click();
        driver.switchTo().alert().accept();
        assertElementPresent(By.id("meldung_222"));

        assertElementPresent(By.id("research_1"));
        assertText(By.id("stat_money"), "4.851,57 €");

        driver.findElement(By.id("link_buero")).click();
        assertText(By.id("b_s_2"), "148,43 €");
    }
}
