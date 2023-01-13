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
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;

import java.util.concurrent.ThreadLocalRandom;
import java.util.concurrent.TimeUnit;

public class PlantageTests extends AbstractTest {
    private static final int USER_ID = ThreadLocalRandom.current().nextInt(1_000_000);

    @BeforeEach
    void beforeEach() {
        resetPlayer(USER_ID, getClass().getSimpleName());
        login("test" + USER_ID);
    }

    @Test
    void testPlantAll2Hours() {
        WebDriver driver = getDriver();

        driver.findElement(By.id("link_plantage")).click();
        setValue(By.id("stunden"), "2");
        assertText(By.id("pr_ko_all"), "Kosten: 1.424,00 €");
        driver.findElement(By.id("plant_all")).submit();
        assertElementPresent(By.id("meldung_207"));
        assertElementPresent(By.id("abort_1"));
        assertElementPresent(By.id("abort_2"));
        assertText(By.id("stat_money"), "13.576,00 €");

        driver.findElement(By.id("link_buero")).click();
        assertText(By.id("b_s_3"), "1.424,00 €");
    }

    @Test
    void testPlantAll13Hours() {
        WebDriver driver = getDriver();

        driver.findElement(By.id("link_plantage")).click();
        setValue(By.id("stunden"), "13");
        assertText(By.id("pr_ko_all"), "Kosten: 9.256,00 €");
        driver.findElement(By.id("plant_all")).submit();
        assertElementPresent(By.id("plant_1"));
        assertElementPresent(By.id("plant_2"));
        assertText(By.id("stat_money"), "15.000,00 €");
    }

    @Test
    void testPlant12HoursManualAmount() {
        WebDriver driver = getDriver();

        driver.findElement(By.id("link_plantage")).click();
        setValue(By.id("amount_1"), "8232");
        driver.findElement(By.id("plant_1")).submit();
        assertElementPresent(By.id("meldung_207"));
        assertElementPresent(By.id("abort_1"));
        assertElementPresent(By.id("plant_2"));
        assertText(By.id("stat_money"), "12.504,00 €");
    }

    @Test
    void testPlantAmountOver12Hours() {
        WebDriver driver = getDriver();

        driver.findElement(By.id("link_plantage")).click();
        setValue(By.id("amount_1"), "8233");
        driver.findElement(By.id("plant_1")).submit();
        assertElementPresent(By.id("meldung_125"));
        assertElementPresent(By.id("plant_1"));
        assertElementPresent(By.id("plant_2"));
        assertText(By.id("stat_money"), "15.000,00 €");
    }

    @Test
    void testPlantAndCancelAfter1Kg() throws InterruptedException {
        WebDriver driver = getDriver();

        driver.findElement(By.id("link_plantage")).click();
        setValue(By.id("amount_15"), "3");
        driver.findElement(By.id("plant_15")).submit();
        assertElementPresent(By.id("meldung_207"));
        assertText(By.id("stat_money"), "14.999,22 €");
        assertElementPresent(By.id("plant_1"));
        assertElementPresent(By.id("abort_15"));
        Thread.sleep(TimeUnit.SECONDS.toMillis(3));
        driver.findElement(By.id("abort_15")).click();
        driver.switchTo().alert().accept();
        assertElementPresent(By.id("meldung_222"));
        assertElementPresent(By.id("plant_15"));
        assertText(By.id("stat_money"), "14.999,22 €");

        driver.findElement(By.id("link_bioladen")).click();
        assertText(By.id("cur_amount_15"), "1 kg");
    }

    @Test
    void testPlantNegativeAmount() {
        WebDriver driver = getDriver();

        driver.findElement(By.id("link_plantage")).click();
        setValue(By.id("amount_1"), "-1");
        Assertions.assertFalse(driver.findElement(By.id("plant_1")).isEnabled());
    }
    @Test
    void testPlantAllNegativeHours() {
        WebDriver driver = getDriver();

        driver.findElement(By.id("link_plantage")).click();
        setValue(By.id("stunden"), "-1");
        driver.findElement(By.id("plant_all")).submit();
        assertElementPresent(By.id("plant_1"));
        assertElementPresent(By.id("plant_2"));
        assertText(By.id("stat_money"), "15.000,00 €");
    }
}
