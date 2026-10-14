package com.kpr.portal.model;

/** Student model (one row of the students table). */
public class Student {

    private int id;
    private String rollNumber;
    private String name;
    private String email;
    private String course;
    private String photoFile;      // file name inside <upload.dir>/photos (may be null)
    private String documentFile;   // file name inside <upload.dir>/documents (may be null)

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public String getRollNumber() { return rollNumber; }
    public void setRollNumber(String rollNumber) { this.rollNumber = rollNumber; }

    public String getName() { return name; }
    public void setName(String name) { this.name = name; }

    public String getEmail() { return email; }
    public void setEmail(String email) { this.email = email; }

    public String getCourse() { return course; }
    public void setCourse(String course) { this.course = course; }

    public String getPhotoFile() { return photoFile; }
    public void setPhotoFile(String photoFile) { this.photoFile = photoFile; }

    public String getDocumentFile() { return documentFile; }
    public void setDocumentFile(String documentFile) { this.documentFile = documentFile; }

    // EL helpers used by the JSP: ${student.hasPhoto}, ${student.hasDocument}
    public boolean isHasPhoto() {
        return photoFile != null && !photoFile.isEmpty();
    }

    public boolean isHasDocument() {
        return documentFile != null && !documentFile.isEmpty();
    }

    /** First letter of the name, shown when there is no photo. */
    public String getInitial() {
        return (name == null || name.isEmpty()) ? "?" : name.substring(0, 1).toUpperCase();
    }
}
