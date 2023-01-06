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

public class PlantageTests extends AbstractTest {
    @Test
    void testPlant2Hours() {
        resetPlayer(12);
        login("test2");
        WebDriver driver = getDriver();

        driver.findElement(By.id("link_plantage")).click();
        setValue(By.id("stunden"), "2");
        assertText(By.id("pr_ko_all"), "Kosten: 824,00 €");
        driver.findElement(By.id("plant_all")).click();
        assertElementPresent(By.id("meldung_207"));
        assertElementPresent(By.id("abort_1"));
        assertElementPresent(By.id("abort_2"));
        assertText(By.id("stat_money"), "4.176,00 €");

        driver.findElement(By.id("link_buero")).click();
        assertText(By.id("b_s_3"), "824,00 €");
    }
}
