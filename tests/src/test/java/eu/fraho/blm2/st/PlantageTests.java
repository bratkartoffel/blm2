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

import java.util.concurrent.TimeUnit;

public class PlantageTests extends AbstractTest {
    @Test
    void testPlantAll2Hours() {
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

    @Test
    void testPlantAll13Hours() {
        resetPlayer(12);
        login("test2");
        WebDriver driver = getDriver();

        driver.findElement(By.id("link_plantage")).click();
        setValue(By.id("stunden"), "13");
        assertText(By.id("pr_ko_all"), "Kosten: 5.356,00 €");
        driver.findElement(By.id("plant_all")).click();
        assertElementPresent(By.id("plant_1"));
        assertElementPresent(By.id("plant_2"));
        assertText(By.id("stat_money"), "5.000,00 €");
    }

    @Test
    void testPlant12HoursManualAmount() {
        resetPlayer(12);
        login("test2");
        WebDriver driver = getDriver();

        driver.findElement(By.id("link_plantage")).click();
        setValue(By.id("amount_1"), "4992");
        driver.findElement(By.id("plant_1")).click();
        assertElementPresent(By.id("meldung_207"));
        assertElementPresent(By.id("abort_1"));
        assertElementPresent(By.id("plant_2"));
        assertText(By.id("stat_money"), "2.504,00 €");
    }

    @Test
    void testPlantAmountOver12Hours() {
        resetPlayer(12);
        login("test2");
        WebDriver driver = getDriver();

        driver.findElement(By.id("link_plantage")).click();
        setValue(By.id("amount_1"), "4993");
        driver.findElement(By.id("plant_1")).click();
        assertElementPresent(By.id("meldung_125"));
        assertElementPresent(By.id("plant_1"));
        assertElementPresent(By.id("plant_2"));
        assertText(By.id("stat_money"), "5.000,00 €");
    }

    @Test
    void testPlantAndCancelAfter1Kg() throws InterruptedException {
        resetPlayer(14);
        login("test4");
        WebDriver driver = getDriver();

        driver.findElement(By.id("link_plantage")).click();
        setValue(By.id("amount_15"), "3");
        driver.findElement(By.id("plant_15")).click();
        assertText(By.id("stat_money"), "4.999,19 €");

        assertElementPresent(By.id("meldung_207"));
        assertElementPresent(By.id("plant_1"));
        assertElementPresent(By.id("abort_15"));
        Thread.sleep(TimeUnit.SECONDS.toMillis(4));
        driver.findElement(By.id("abort_15")).click();
        driver.switchTo().alert().accept();
        assertElementPresent(By.id("meldung_222"));
        assertElementPresent(By.id("plant_15"));
        assertText(By.id("stat_money"), "4.999,19 €");

        driver.findElement(By.id("link_bioladen")).click();
        assertText(By.id("cur_amount_15"), "1 kg");
    }

    @Test
    void testPlantNegativeAmount() {
        resetPlayer(12);
        login("test2");
        WebDriver driver = getDriver();

        driver.findElement(By.id("link_plantage")).click();
        setValue(By.id("amount_1"), "-1");
        driver.findElement(By.id("plant_1")).click();
        assertElementPresent(By.id("plant_1"));
        assertElementPresent(By.id("plant_2"));
        assertText(By.id("stat_money"), "5.000,00 €");
    }
    @Test
    void testPlantAllNegativeHours() {
        resetPlayer(12);
        login("test2");
        WebDriver driver = getDriver();

        driver.findElement(By.id("link_plantage")).click();
        setValue(By.id("stunden"), "-1");
        driver.findElement(By.id("plant_all")).click();
        assertElementPresent(By.id("plant_1"));
        assertElementPresent(By.id("plant_2"));
        assertText(By.id("stat_money"), "5.000,00 €");
    }
}
