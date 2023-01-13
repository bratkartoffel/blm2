/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
package eu.fraho.blm2.st;

import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;

import java.util.concurrent.ThreadLocalRandom;
import java.util.concurrent.TimeUnit;

public class BuildingTests extends AbstractTest {
    private static final int USER_ID = ThreadLocalRandom.current().nextInt(1_000_000);

    @BeforeEach
    void beforeEach() {
        resetPlayer(USER_ID, getClass().getSimpleName());
        login("test" + USER_ID);
    }

    @Test
    void testBuildAndCancel() {
        WebDriver driver = getDriver();

        driver.findElement(By.id("link_gebaeude")).click();

        assertText(By.id("stat_money"), "5.000,00 €");
        driver.findElement(By.id("build_1")).click();
        assertElementPresent(By.id("meldung_207"));

        assertText(By.id("stat_money"), "1.127,63 €");
        driver.findElement(By.id("abort_1")).click();
        driver.switchTo().alert().accept();
        assertElementPresent(By.id("meldung_222"));

        assertElementPresent(By.id("build_1"));
        assertText(By.id("stat_money"), "4.031,91 €");
    }

    @Test
    void testBuild() throws InterruptedException {
        WebDriver driver = getDriver();

        driver.findElement(By.id("link_gebaeude")).click();

        assertText(By.id("stat_money"), "5.000,00 €");
        driver.findElement(By.id("build_2")).click();
        assertElementPresent(By.id("meldung_207"));

        assertText(By.id("stat_money"), "4.561,60 €");
        Thread.sleep(TimeUnit.SECONDS.toMillis(2));

        driver.findElement(By.id("link_gebaeude")).click();
        assertElementPresent(By.id("build_2"));
        assertText(By.id("g2"), "Forschungszentrum (Stufe 1)");

        driver.findElement(By.id("link_buero")).click();
        assertText(By.id("b_s_1"), "438,40 €");
        assertText(By.id("b_p_1"), "126");
        assertText(By.id("b_p_4"), "126");
        assertText(By.id("b_p_7"), "126");
    }
}
