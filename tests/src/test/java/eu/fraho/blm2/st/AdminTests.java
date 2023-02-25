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

import java.io.File;
import java.util.Objects;

public class AdminTests extends AbstractTest {
    private final int userId = getNextUserId();

    @BeforeEach
    void beforeEach(TestInfo testInfo) {
        resetPlayer(userId, testInfo);
        login("test" + userId);
    }

    @Test
    void testImportLegacyUser() {
        WebDriver driver = getDriver();
        File importfile = new File(Objects.requireNonNull(AdminTests.class.getResource("/export_test-import-no-metadata.zip")).getFile());

        driver.findElement(By.id("link_admin")).click();
        driver.findElement(By.id("link_admin_benutzer")).click();
        driver.findElement(By.id("link_admin_benutzer_importieren")).click();

        setCheckbox(By.id("verify"), true);
        setCheckbox(By.id("new_id"), false);
        setCheckbox(By.id("ignore_round"), true);
        setCheckbox(By.id("with_logs"), false);
        setCheckbox(By.id("ignore_metadata"), true);
        driver.findElement(By.id("import")).sendKeys(importfile.toString());
        driver.findElement(By.id("do_import")).click();
        assertElementPresent(By.id("meldung_249"));

        driver.get("http://%s/?p=profil&id=5".formatted(LOCALHOST));
        assertElementPresent(By.id("profile_5"));
    }

    @Test
    void testImportLegacyUserNewId() {
        WebDriver driver = getDriver();
        File importfile = new File(Objects.requireNonNull(AdminTests.class.getResource("/export_test-import-no-metadata.zip")).getFile());

        driver.findElement(By.id("link_admin")).click();
        driver.findElement(By.id("link_admin_benutzer")).click();
        driver.findElement(By.id("link_admin_benutzer_importieren")).click();

        setCheckbox(By.id("verify"), true);
        setCheckbox(By.id("new_id"), true);
        setCheckbox(By.id("ignore_round"), true);
        setCheckbox(By.id("with_logs"), false);
        setCheckbox(By.id("ignore_metadata"), true);
        driver.findElement(By.id("import")).sendKeys(importfile.toString());
        driver.findElement(By.id("do_import")).click();
        assertElementPresent(By.id("meldung_249"));

        driver.get("http://%s/?p=profil&id=5".formatted(LOCALHOST));
        assertElementPresent(By.id("meldung_154"));
    }

    @Test
    void testImportUser() {
        WebDriver driver = getDriver();
        File importfile = new File(Objects.requireNonNull(AdminTests.class.getResource("/export_test-import.zip")).getFile());

        driver.findElement(By.id("link_admin")).click();
        driver.findElement(By.id("link_admin_benutzer")).click();
        driver.findElement(By.id("link_admin_benutzer_importieren")).click();

        setCheckbox(By.id("verify"), true);
        setCheckbox(By.id("new_id"), false);
        setCheckbox(By.id("ignore_round"), true);
        setCheckbox(By.id("with_logs"), false);
        setCheckbox(By.id("ignore_metadata"), false);
        driver.findElement(By.id("import")).sendKeys(importfile.toString());
        driver.findElement(By.id("do_import")).click();
        assertElementPresent(By.id("meldung_249"));

        driver.get("http://%s/?p=profil&id=5".formatted(LOCALHOST));
        assertElementPresent(By.id("profile_5"));
    }

    @Test
    void testImportUserAltered() {
        WebDriver driver = getDriver();
        File importfile = new File(Objects.requireNonNull(AdminTests.class.getResource("/export_test-import-altered.zip")).getFile());

        driver.findElement(By.id("link_admin")).click();
        driver.findElement(By.id("link_admin_benutzer")).click();
        driver.findElement(By.id("link_admin_benutzer_importieren")).click();

        setCheckbox(By.id("verify"), true);
        setCheckbox(By.id("new_id"), false);
        setCheckbox(By.id("ignore_round"), true);
        setCheckbox(By.id("with_logs"), false);
        setCheckbox(By.id("ignore_metadata"), false);
        driver.findElement(By.id("import")).sendKeys(importfile.toString());
        driver.findElement(By.id("do_import")).click();
        assertElementPresent(By.id("meldung_176"));

        driver.get("http://%s/?p=profil&id=5".formatted(LOCALHOST));
        assertElementPresent(By.id("meldung_154"));
    }
}
